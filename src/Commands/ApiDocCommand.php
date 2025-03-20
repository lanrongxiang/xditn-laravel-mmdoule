<?php

namespace Xditn\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Knuckles\Camel\Camel;
use Knuckles\Scribe\Matching\RouteMatcherInterface;
use Knuckles\Scribe\Tools\ConsoleOutputUtils as c;
use Knuckles\Scribe\Tools\DocumentationConfig;
use Knuckles\Scribe\Tools\Globals;
use Knuckles\Scribe\Tools\PathConfig;
use Knuckles\Scribe\Writing\Writer;
use Xditn\Facade\Module;
use Xditn\Support\ApiEndPoints;

/**
 * API 文档生成命令
 *
 * 用于生成 mmodule 的 API 文档，支持 VitePress 和 Postman 的 JSON 文件格式。
 */
class ApiDocCommand extends Command
{
    protected $signature = 'xditn:api:doc
                            {--config=xditn_api_doc : 选择使用哪个配置文件, 默认为 config/xditn_api_doc.php }
                            {--no-vitepress : 不生成 VitePress API 文档 }
                            {--no-postman-json : 不生成 Postman Json 文件 }';

    protected $description = '生成 mmodule API 文档';

    protected DocumentationConfig $docConfig;

    protected PathConfig $paths;

    public function handle(RouteMatcherInterface $routeMatcher): void
    {
        $noVitepress = $this->option('no-vitepress');
        $noPostmanJson = $this->option('no-postman-json');
        $this->bootstrap();
        $apiEndPoints = new ApiEndPoints($this, $routeMatcher, $this->paths);
        $extractedEndpoints = $apiEndPoints->get();
        $userDefinedEndpoints = Camel::loadUserDefinedEndpoints(Camel::camelDir($this->paths));
        $groupedEndpoints = $this->mergeUserDefinedEndpoints($extractedEndpoints, $userDefinedEndpoints);
        // 是否生成 vitepress doc
        if (! $noVitepress) {

            $apiDocBasePath = $this->docConfig->get('base_path');
            $apiDocPath = $apiDocBasePath.DIRECTORY_SEPARATOR.'docs'.DIRECTORY_SEPARATOR.'api';
            // 侧边栏 ts 文件
            $siderTs = $apiDocBasePath.DIRECTORY_SEPARATOR.'docs'.DIRECTORY_SEPARATOR.'.vitepress'.DIRECTORY_SEPARATOR.'siders'.DIRECTORY_SEPARATOR.'sider.ts';
            if (! is_dir($apiDocPath)) {
                mkdir($apiDocPath, 0755, true);
            }
            $modules = [];
            foreach ($groupedEndpoints as $endpoint) {
                // 如果没有任何 endpoints，跳过
                if (!count($endpoint['endpoints'])) {
                    continue;
                }

                foreach ($endpoint['endpoints'] as $k => $item) {
                    // 获取控制器的命名空间，并转换为小写
                    $controllerNamespace = Str::of($item['controller']->getName())->lower()->explode('\\');

                    // 获取模块名，通常是命名空间的第二个部分
                    $module = $controllerNamespace->get(1);

                    // 获取控制器的目录路径（支持多级目录）
                    $controllerNamespaceParts = $controllerNamespace->slice(2)->implode(DIRECTORY_SEPARATOR);
                    $modulePath = $apiDocPath . DIRECTORY_SEPARATOR . $module . ($controllerNamespaceParts ? DIRECTORY_SEPARATOR . $controllerNamespaceParts : '');

                    // 创建模块目录，如果不存在
                    if (!is_dir($modulePath)) {
                        mkdir($modulePath, 0755, true);
                    }

                    // 获取控制器类名，并移除 'Controller' 后缀
                    $controller = $controllerNamespace->pop();
                    $controller = str_replace(['Controller', 'controller'], ['', ''], $controller);

                    // 拼接控制器路径
                    $controllerPath = $modulePath . DIRECTORY_SEPARATOR . $controller;
                    if (!is_dir($controllerPath)) {
                        mkdir($controllerPath, 0755, true);
                    }

                    // 获取方法名
                    $method = $item['method']->getName();
                    $md = $controllerPath . DIRECTORY_SEPARATOR . $method . '.md';

                    // 获取接口的标题
                    $title = $item['metadata']['title'];

                    // 初始化模块信息，如果不存在的话
                    if (!isset($modules[$module])) {
                        $modules[$module] = [
                            'title' => $item['metadata']['groupName'],
                            'children' => [],
                        ];
                    }

                    // 初始化控制器信息，如果不存在的话
                    if (!isset($modules[$module]['children'][$controller])) {
                        $modules[$module]['children'][$controller] = [
                            'title' => strlen($item['metadata']['subgroup']) ? $item['metadata']['subgroup'] : $controller,
                            'children' => [],
                        ];
                    }

                    // 将接口信息添加到控制器的子级中
                    $modules[$module]['children'][$controller]['children'][] = [
                        'text' => strlen($title) ? $title : $item['uri'],
                        'link' => '/docs/api/' . $module . '/' . $controller . '/' . $method,
                        'markdown_content' => $this->generateVitepressMarkdown($item, $k + 1),
                        'markdown_file' => $md,
                    ];
                }
            }
            $modulesByName = [];
            Module::all()->each(function ($module) use (&$modulesByName) {
                $modulesByName[strtolower($module['name'])] = $module['title'];
            });
            $sideBars = [];
            foreach ($modules as $moduleName => $module) {
                $controllers = $module['children'];
                $sideBars[$moduleName] = [
                    'text' => $modulesByName[strtolower($moduleName)] ?? $module['title'],
                    'collapsed' => true,
                ];
                $items = [];
                foreach ($controllers as $controller => $methods) {
                    $items[$controller] = [
                        'text' => $methods['title'] ?? $controller,
                    ];
                    foreach ($methods['children'] as $method) {
                        $items[$controller]['items'][] = [
                            'text' => $method['text'],
                            'link' => $method['link'],
                        ];
                        file_put_contents($method['markdown_file'], $method['markdown_content']);
                    }
                }
                $sideBars[$moduleName]['items'] = array_values($items);
            }
            file_put_contents(
                $siderTs,
                'export default '.json_encode(array_values($sideBars), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }
        // 生成 Postman Json 文件
        if (! $noPostmanJson) {
            $configFileOrder = $this->docConfig->get('groups.order', []);
            $groupedEndpoints = Camel::prepareGroupedEndpointsForOutput($groupedEndpoints, $configFileOrder);
            $this->generatePostmanJson($groupedEndpoints);
        }
        $this->info('生成  API 文档成功');
    }

    protected function generateVitepressMarkdown($item, $index): string
    {
        $httpMethod = implode(' | ', $item['httpMethods']);
        if ($httpMethod === 'GET') {
            $params = $this->parseParams($item['queryParameters']);
        } else {
            $params = $this->parseParams($item['bodyParameters']);
        }
        $urlParams = $this->parseParams($item['urlParameters']);
        $headers = $this->parseHeaders($item['headers'], $item['metadata']['authenticated']);
        $md = <<<MARKDOWN
---
sidebar_position: $index
---


# {$item['metadata']['title']}

MARKDOWN;
        if ($item['metadata']['description']) {
            $md .= <<<MARKDOWN
:::info
{$item['metadata']['description']}
:::

MARKDOWN;
        }
        $md .= <<<MARKDOWN
<ApiEndpoint method="{$httpMethod}" path="{$item['uri']}"/>
MARKDOWN;
        if ($headers) {
            $md .= <<<MARKDOWN
<ApiHeader :headers="$headers"/>
MARKDOWN;
        }
        if ($urlParams) {
            $md .= <<<MARKDOWN
    <ApiParameters :params="$urlParams" :type="3"/>
MARKDOWN;
        }
        if ($params) {
            if ($httpMethod === 'GET') {
                $md .= <<<MARKDOWN
    <ApiParameters :params="$params" :type="2"/>
MARKDOWN;
            } else {
                $md .= <<<MARKDOWN
    <ApiParameters :params="$params" :type="1"/>
MARKDOWN;
            }
        }
        $responseFields = $this->parseResponseFields($item['responseFields']);
        $md .= <<<MARKDOWN
    <ApiResponse :params="$responseFields"/>
MARKDOWN;

        return $md;
    }

    /**
     * 解析 params
     *
     * @param $params
     * @return string
     */
    protected function parseParams($params): string
    {
        if (! count($params)) {
            return '';
        }
        $object = Str::of('[');
        foreach ($params as $value) {
            $object = $object->append(
                sprintf(
                    "{name: '%s', type: '%s', required: %s, description: '%s'},",
                    $value['name'],
                    $value['type'],
                    $value['required'] ? 'true' : 'false',
                    $value['description']
                )
            );
        }

        return $object->trim(',')->append(']')->toString();
    }

    /**
     * 解析响应字段
     *
     * @param $fields
     * @return string
     */
    protected function parseResponseFields($fields): string
    {
        if (! count($fields)) {
            return '[]';
        }
        $object = Str::of('[');
        foreach ($fields as $value) {
            $object = $object->append(
                sprintf(
                    "{name: '%s', type: '%s',  description: '%s'},",
                    $value['name'],
                    $value['type'],
                    $value['description']
                )
            );
        }

        return $object->trim(',')->append(']')->toString();
    }

    /**
     * 解析 headers
     *
     * @param $headers
     * @param $isAuth
     * @return string
     */
    protected function parseHeaders($headers, $isAuth): string
    {
        if (! count($headers)) {
            return '[]';
        }
        $object = Str::of('[');
        foreach ($headers as $k => $value) {
            if (! $isAuth && $k === 'Authorization') {
                continue;
            }
            $object = $object->append(sprintf("{key: '%s', value: '%s'},", $k, $value));
        }

        return $object->trim(',')->append(']')->toString();
    }

    /**
     * @return DocumentationConfig
     */
    public function getDocConfig(): DocumentationConfig
    {
        return $this->docConfig;
    }

    /**
     * @return void
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
        // Force root URL so it works in Postman collection
        $baseUrl = $this->docConfig->get('base_url') ?? config('app.url');
        URL::forceRootUrl($baseUrl);
    }

    protected function mergeUserDefinedEndpoints(array $groupedEndpoints, array $userDefinedEndpoints): array
    {
        foreach ($userDefinedEndpoints as $endpoint) {
            $indexOfGroupWhereThisEndpointShouldBeAdded = Arr::first(
                array_keys($groupedEndpoints), function ($key) use ($groupedEndpoints, $endpoint) {
                $group = $groupedEndpoints[$key];

                return $group['name'] === ($endpoint['metadata']['groupName'] ?? $this->docConfig->get(
                        'groups.default',
                        ''
                    ));
            }
            );
            if ($indexOfGroupWhereThisEndpointShouldBeAdded !== null) {
                $groupedEndpoints[$indexOfGroupWhereThisEndpointShouldBeAdded]['endpoints'][] = $endpoint;
            } else {
                $newGroup = [
                    'name' => $endpoint['metadata']['groupName'] ?? $this->docConfig->get('groups.default', ''),
                    'description' => $endpoint['metadata']['groupDescription'] ?? null,
                    'endpoints' => [$endpoint],
                ];
                $groupedEndpoints[$newGroup['name']] = $newGroup;
            }
        }

        return $groupedEndpoints;
    }

    /**
     * 生成 Postman Json 文件
     *
     * @param  array  $groups
     * @return void
     */
    protected function generatePostmanJson(array $groups): void
    {
        // 确保目录存在
        $postmanPath = $this->docConfig->get('base_path').DIRECTORY_SEPARATOR.'postman.json';
        $postmanDir = dirname($postmanPath);

        if (! is_dir($postmanDir)) {
            mkdir($postmanDir, 0755, true); // 创建目录，递归创建中间目录
        }

        $writer = new class($this->docConfig, $this->paths) extends Writer
        {
            public function postmanJson(array $groups): void
            {
                $collection = $this->generatePostmanCollection($groups);
                // 注意：$this->config->get('base_path') 应该已经确保是存在的
                file_put_contents($this->config->get('base_path').DIRECTORY_SEPARATOR.'postman.json', $collection);
            }
        };

        $writer->postmanJson($groups);
    }
}
