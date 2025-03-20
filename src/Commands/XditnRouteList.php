<?php

declare(strict_types=1);

namespace Xditn\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionFunction;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Terminal;

class XditnRouteList extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'xditn:route:list {--app : 显示应用路由列表} {--json : 以JSON格式输出路由列表}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '列出所有已注册的路由';

    /**
     * 路由实例
     *
     * @var Router
     */
    protected Router $router;

    /**
     * 表头
     *
     * @var string[]
     */
    protected array $headers = ['Domain', 'Method', 'URI', 'Name', 'Action', 'Middleware'];

    /**
     * 终端宽度解析回调
     *
     * @var Closure|null
     */
    protected static ?Closure $terminalWidthResolver = null;

    /**
     * 动词颜色
     *
     * @var array
     */
    protected array $verbColors = [
        'ANY' => 'red',
        'GET' => 'blue',
        'HEAD' => '#6C7280',
        'OPTIONS' => '#6C7280',
        'POST' => 'yellow',
        'PUT' => 'yellow',
        'PATCH' => 'yellow',
        'DELETE' => 'red',
    ];

    /**
     * 创建新的路由命令实例。
     *
     * @param  Router  $router 路由实例
     */
    public function __construct(Router $router)
    {
        parent::__construct();
        $this->router = $router;
    }

    /**
     * 执行命令的主要逻辑。
     *
     * @return void
     */
    public function handle(): void
    {
        $this->router->flushMiddlewareGroups();
        $this->reloadAppRouteList();
        $routes = $this->getRoutes();
        if (empty($routes)) {
            $this->components->error('您的应用没有任何路由。');

            return;
        }
        $this->displayRoutes($routes);
    }

    /**
     * 重新加载路由列表。
     *
     * @return void
     */
    protected function reloadAppRouteList(): void
    {
        if ($this->option('app')) {
            require app()->getCachedRoutesPath();
        }
    }

    /**
     * 获取已注册路由的信息。
     *
     * @return array
     */
    protected function getRoutes(): array
    {
        return collect($this->router->getRoutes())->map(fn (Route $route) => $this->getRouteInformation($route))->filter(
        )->all();
    }

    /**
     * 获取给定路由的信息。
     *
     * @param  Route  $route 路由实例
     * @return array 路由信息
     *
     * @throws \ReflectionException
     */
    protected function getRouteInformation(Route $route): array
    {
        return [
            'domain' => $route->domain(),
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => ltrim($route->getActionName(), '\\'),
            'middleware' => $this->getMiddleware($route),
            'vendor' => $this->isVendorRoute($route),
        ];
    }

    /**
     * 获取路由的中间件。
     *
     * @param  Route  $route 路由实例
     * @return string 中间件列表
     */
    protected function getMiddleware(Route $route): string
    {
        return collect($this->router->gatherRouteMiddleware($route))->map(
            fn ($middleware) => $middleware instanceof Closure ? 'Closure' : $middleware
        )->implode("\n");
    }

    /**
     * 判断路由是否定义在应用外部。
     *
     * @param  Route  $route 路由实例
     * @return bool 如果是供应商路由则返回 true，否则返回 false
     *
     * @throws \ReflectionException
     */
    protected function isVendorRoute(Route $route): bool
    {
        $uses = $route->action['uses'];
        $path = $uses instanceof Closure ? (new ReflectionFunction($uses))->getFileName() : (new ReflectionClass(
            $route->getControllerClass()
        ))->getFileName();

        return str_starts_with($path, base_path('vendor'));
    }

    /**
     * 在控制台上显示路由信息。
     *
     * @param  array  $routes 路由列表
     * @return void
     */
    protected function displayRoutes(array $routes): void
    {
        $output = $this->option('json') ? $this->asJson(collect($routes)) : $this->forCli(collect($routes));
        $this->output->writeln($output);
    }

    /**
     * 将给定路由转换为 JSON 格式。
     *
     * @param  Collection  $routes 路由集合
     * @return string JSON 格式的路由信息
     */
    protected function asJson(Collection $routes): string
    {
        return $routes->map(fn ($route) => [
            ...$route,
            'middleware' => explode("\n", $route['middleware'] ?? ''),
        ])->values()->toJson();
    }

    /**
     * 将给定路由转换为常规 CLI 输出。
     *
     * @param  Collection  $routes 路由集合
     * @return array 格式化的 CLI 输出
     */
    protected function forCli(Collection $routes): array
    {
        $maxMethodLength = mb_strlen($routes->max('method'));
        $terminalWidth = $this->getTerminalWidth();

        return $routes->map(fn ($route) => $this->formatRouteForCli($route, $maxMethodLength, $terminalWidth))->flatten(
        )->filter()->prepend('')->push('')->push(
            $this->determineRouteCountOutput($routes, $terminalWidth)
        )->toArray();
    }

    /**
     * 格式化单个路由以用于 CLI 输出。
     *
     * @param  array  $route           路由信息
     * @param  int  $maxMethodLength 最大方法名长度
     * @param  int  $terminalWidth   终端宽度
     * @return array 格式化后的路由信息
     */
    protected function formatRouteForCli(array $route, int $maxMethodLength, int $terminalWidth): array
    {
        $spaces = str_repeat(' ', max($maxMethodLength + 6 - mb_strlen($route['method']), 0));
        $dots = str_repeat(
            '.',
            max($terminalWidth - mb_strlen($route['method'].$spaces.$route['uri'].$route['action']) - 6, 0)
        );
        $method = Str::of($route['method'])->explode('|')->map(
            fn ($method) => sprintf('<fg=%s>%s</>', $this->verbColors[$method] ?? 'default', $method)
        )->implode('<fg=#6C7280>|</>');

        return [
            sprintf(
                '  <fg=white;options=bold>%s</> %s<fg=white>%s</><fg=#6C7280>%s %s</>',
                $method,
                $spaces,
                preg_replace('#({[^}]+})#', '<fg=yellow>$1</>', $route['uri']),
                $dots,
                $route['action']
            ),
            $this->output->isVerbose() && ! empty($route['middleware']) ? "<fg=#6C7280>{$route['middleware']}</>" : null,
        ];
    }

    /**
     * 确定并返回在 CLI 输出中显示的路由数量。
     *
     * @param  Collection  $routes        路由集合
     * @param  int  $terminalWidth 终端宽度
     * @return string 路由数量信息
     */
    protected function determineRouteCountOutput(Collection $routes, int $terminalWidth): string
    {
        $routeCountText = '显示 ['.$routes->count().'] 条路由';
        $offset = $terminalWidth - mb_strlen($routeCountText) - 2;

        return str_repeat(' ', $offset).'<fg=blue;options=bold>'.$routeCountText.'</>';
    }

    /**
     * 获取终端宽度。
     *
     * @return int 终端宽度
     */
    public static function getTerminalWidth(): int
    {
        return is_null(static::$terminalWidthResolver) ? (new Terminal())->getWidth() : call_user_func(
            static::$terminalWidthResolver
        );
    }

    /**
     * 设置用于解析终端宽度的回调。
     *
     * @param  Closure|null  $resolver 解析回调
     * @return void
     */
    public static function resolveTerminalWidthUsing(?Closure $resolver): void
    {
        static::$terminalWidthResolver = $resolver;
    }

    /**
     * 获取控制台命令选项。
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['app', null, InputOption::VALUE_NONE, '显示应用路由列表'],
            ['json', null, InputOption::VALUE_NONE, '以JSON格式输出路由列表'],
            ['method', null, InputOption::VALUE_OPTIONAL, '按方法过滤路由'],
            ['name', null, InputOption::VALUE_OPTIONAL, '按名称过滤路由'],
            ['domain', null, InputOption::VALUE_OPTIONAL, '按域名过滤路由'],
            [
                'path',
                null,
                InputOption::VALUE_OPTIONAL,
                '仅显示与给定路径模式匹配的路由',
            ],
            [
                'except-path',
                null,
                InputOption::VALUE_OPTIONAL,
                '不显示与给定路径模式匹配的路由',
            ],
            ['reverse', 'r', InputOption::VALUE_NONE, '反转路由的排序'],
            [
                'sort',
                null,
                InputOption::VALUE_OPTIONAL,
                '按列（domain, method, uri, name, action, middleware）排序',
                'uri',
            ],
            [
                'except-vendor',
                null,
                InputOption::VALUE_NONE,
                '不显示由供应商包定义的路由',
            ],
            [
                'only-vendor',
                null,
                InputOption::VALUE_NONE,
                '仅显示由供应商包定义的路由',
            ],
        ];
    }
}
