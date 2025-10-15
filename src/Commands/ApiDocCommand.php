<?php

namespace Xditn\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Knuckles\Camel\Camel;
use Knuckles\Scribe\Matching\RouteMatcherInterface;
use Knuckles\Scribe\Tools\ConsoleOutputUtils as c;
use Knuckles\Scribe\Tools\DocumentationConfig;
use Knuckles\Scribe\Tools\Globals;
use Knuckles\Scribe\Tools\PathConfig;
use Knuckles\Scribe\Writing\Writer;
use Xditn\Support\ApiEndPoints;

/**
 * API 文档生成命令
 *
 * 用于生成 mmodule 的 API 文档，目前只生成 Postman 的 JSON 文件格式，
 * 并在生成的文件中对于 POST 请求的 body 参数使用 formdata 模式生成，
 * 每个参数包含 key、value、description 和 type 属性。
 */
class ApiDocCommand extends Command
{
    // 仅保留 config 配置选项
    protected $signature = 'xditn:api:doc
                            {--config=xditn_api_doc : 选择使用哪个配置文件, 默认为 config/xditn_api_doc.php }';

    protected $description = '生成 mmodule API 文档（仅生成 Postman Json 文件）';

    protected DocumentationConfig $docConfig;

    protected PathConfig $paths;

    /**
     * 命令入口，保留原方法名
     *
     * @param  RouteMatcherInterface  $routeMatcher
     * @return void
     */
    public function handle(RouteMatcherInterface $routeMatcher): void
    {
        $this->bootstrap();

        // 获取所有 API endpoints
        $apiEndPoints = new ApiEndPoints($this, $routeMatcher, $this->paths);
        $extractedEndpoints = $apiEndPoints->get();
        $userDefinedEndpoints = Camel::loadUserDefinedEndpoints(Camel::camelDir($this->paths));

        // 合并用户自定义 endpoints
        $groupedEndpoints = $this->mergeUserDefinedEndpoints($extractedEndpoints, $userDefinedEndpoints);

        // 注入详细接口文档说明，并对 POST 请求生成 formdata 格式的 body 参数
        $groupedEndpoints = $this->injectDocumentation($groupedEndpoints);

        // 生成 Postman Json 文件
        $configFileOrder = $this->docConfig->get('groups.order', []);
        $groupedEndpoints = Camel::prepareGroupedEndpointsForOutput($groupedEndpoints, $configFileOrder);
        $this->generatePostmanJson($groupedEndpoints);

        $this->info('已生成 Postman API 文档文件');
    }

    /**
     * 合并用户自定义的 Endpoints 到已提取的 endpoints 中
     *
     * @param  array  $groupedEndpoints
     * @param  array  $userDefinedEndpoints
     * @return array
     */
    protected function mergeUserDefinedEndpoints(array $groupedEndpoints, array $userDefinedEndpoints): array
    {
        foreach ($userDefinedEndpoints as $endpoint) {
            // 获取该 endpoint 应归属的分组名称
            $groupName = $endpoint['metadata']['groupName'] ?? $this->docConfig->get('groups.default', '');
            // 判断该分组是否存在
            $groupKey = Arr::first(array_keys($groupedEndpoints), fn ($key) => $groupedEndpoints[$key]['name'] === $groupName);
            if ($groupKey !== null) {
                $groupedEndpoints[$groupKey]['endpoints'][] = $endpoint;
            } else {
                $groupedEndpoints[$groupName] = [
                    'name' => $groupName,
                    'description' => $endpoint['metadata']['groupDescription'] ?? null,
                    'endpoints' => [$endpoint],
                ];
            }
        }

        return $groupedEndpoints;
    }

    /**
     * 在各个 endpoint 中注入详细的接口文档说明，
     * 包括 body 参数、query 参数和 response 字段，并对 POST 请求生成 formdata 格式的 body 参数。
     *
     * @param  array  $groups
     * @return array
     */
    protected function injectDocumentation(array $groups): array
    {
        foreach ($groups as &$group) {
            if (empty($group['endpoints']) || ! is_array($group['endpoints'])) {
                continue;
            }
            foreach ($group['endpoints'] as &$endpoint) {
                $desc = $endpoint['metadata']['description'] ?? '';
                if (! empty($endpoint['bodyParameters'])) {
                    $desc .= "\n\n【Body 参数】\n";
                    foreach ($endpoint['bodyParameters'] as $param) {
                        $desc .= sprintf("%s (%s): %s\n", $param['name'], $param['type'], $param['description'] ?? '');
                    }
                }
                if (! empty($endpoint['queryParameters'])) {
                    $desc .= "\n【Query 参数】\n";
                    foreach ($endpoint['queryParameters'] as $param) {
                        $desc .= sprintf("%s (%s): %s\n", $param['name'], $param['type'], $param['description'] ?? '');
                    }
                }
                if (! empty($endpoint['responseFields'])) {
                    $desc .= "\n【Response 字段】\n";
                    foreach ($endpoint['responseFields'] as $field) {
                        $desc .= sprintf("%s (%s): %s\n", $field['name'], $field['type'], $field['description'] ?? '');
                    }
                }
                $endpoint['metadata']['description'] = trim($desc);

                // 若为 POST 请求且存在 body 参数，生成 formdata 格式的请求体
                if (in_array('POST', $endpoint['httpMethods']) && ! empty($endpoint['bodyParameters'])) {
                    $endpoint['body'] = [
                        'mode' => 'formdata',
                        'formdata' => array_map(function ($param) {
                            return [
                                'key' => $param['name'],
                                'value' => '',
                                'description' => $param['description'] ?? '',
                                'type' => 'text',
                            ];
                        }, $endpoint['bodyParameters']),
                    ];
                }
            }
        }

        return $groups;
    }

    /**
     * 生成 Postman Json 文件
     *
     * @param  array  $groups
     * @return void
     */
    protected function generatePostmanJson(array $groups): void
    {
        // 确保目标目录存在
        $postmanPath = $this->docConfig->get('base_path').DIRECTORY_SEPARATOR.'postman.json';
        $postmanDir = dirname($postmanPath);
        if (! is_dir($postmanDir)) {
            mkdir($postmanDir, 0755, true);
        }

        // 使用匿名类继承 Writer，保留原有方法名
        $writer = new class($this->docConfig, $this->paths) extends Writer
        {
            /**
             * 生成 Postman JSON 文件并写入磁盘
             *
             * @param  array  $groups
             * @return void
             */
            public function postmanJson(array $groups): void
            {
                $collection = $this->generatePostmanCollection($groups);
                file_put_contents($this->config->get('base_path').DIRECTORY_SEPARATOR.'postman.json', $collection);
            }
        };

        $writer->postmanJson($groups);
    }

    /**
     * 初始化配置和路径信息
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function bootstrap(): void
    {
        Globals::$shouldBeVerbose = $this->option('verbose');
        c::bootstrapOutput($this->output);

        $configName = $this->option('config');
        if (! config($configName)) {
            throw new \InvalidArgumentException("(config/{$configName}.php) 配置文件不存在");
        }
        $this->paths = new PathConfig($configName);
        $this->docConfig = new DocumentationConfig(config($this->paths->configName));

        // 强制设置根 URL，确保在 Postman 集合中使用正确的 base_url
        $baseUrl = $this->docConfig->get('base_url') ?? config('app.url');
        URL::forceRootUrl($baseUrl);
    }

    /**
     * 获取 DocumentationConfig 对象
     *
     * @return DocumentationConfig
     */
    public function getDocConfig(): DocumentationConfig
    {
        return $this->docConfig;
    }
}
