# Laravel Multi-Module Manager (Xditn)

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.2-blue.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-%3E%3D11-red.svg)](https://laravel.com)

> 一个强大的 Laravel 多模块管理系统，帮助你快速构建模块化、可扩展的企业级应用。

## 📖 简介

Xditn Laravel Multi-Module 是一个专为 Laravel 11+ 设计的模块化开发框架，提供完整的模块生命周期管理、自动化代码生成、API 文档生成、权限控制等功能。它可以帮助你：

- 🏗️ **模块化架构**：将大型应用拆分为独立、可复用的模块
- 🚀 **快速开发**：通过 Artisan 命令快速生成模块、模型、控制器等
- 📝 **自动化文档**：自动生成 Postman Collection API 文档
- 🔐 **权限管理**：基于 RBAC 的细粒度权限控制（字段级别）
- 🔄 **灵活存储**：支持文件和数据库两种模块存储方式
- 🎨 **统一响应**：标准化的 JSON API 响应格式
- 💪 **企业级特性**：软删除、数据范围、字段访问控制、树形结构等

## ✨ 核心特性

### 1. 模块管理

- ✅ 模块自动发现与加载
- ✅ 模块安装/卸载/启用/禁用
- ✅ 模块依赖管理
- ✅ 模块优先级排序

### 2. 代码生成器

- ✅ 控制器生成（CRUD）
- ✅ 模型生成（基于数据表）
- ✅ 事件/监听器生成
- ✅ 数据库迁移生成
- ✅ Seeder 生成

### 3. 增强的 Eloquent 模型

- ✅ 自动字段过滤（可读/可写）
- ✅ 快速搜索（模糊搜索）
- ✅ 数据范围（根据用户权限）
- ✅ 软删除增强
- ✅ 树形结构支持
- ✅ 关联关系自动处理
- ✅ 时间戳格式化

### 4. API 开发

- ✅ 统一的响应格式
- ✅ 自动 API 文档生成（Postman）
- ✅ Laravel Sanctum 认证
- ✅ 请求日志记录
- ✅ 异常统一处理

### 5. 集成功能

- ✅ 微信开发（EasyWeChat）
- ✅ 支付集成（支付宝/微信支付）
- ✅ Excel 导入导出（Maatwebsite）
- ✅ SQL 日志监听

## 🔧 环境要求

| 依赖     | 版本要求                                                                    |
| -------- | --------------------------------------------------------------------------- |
| PHP      | >= 8.2                                                                      |
| Laravel  | >= 11.0                                                                     |
| Composer | >= 2.0                                                                      |
| MySQL    | >= 5.7 或 MariaDB >= 10.3                                                   |
| 扩展     | pdo, pdo_mysql, zip, bcmath, ctype, json, mbstring, openssl, tokenizer, xml |

## 📦 安装

### 1. 通过 Composer 安装

```bash
composer require xditn/laravel-mmodule
```

### 2. 运行安装命令

```bash
php artisan xditn:install
```

安装向导将引导你完成：

- 数据库配置
- 应用 URL 配置
- 默认模块选择
- 超级管理员创建

### 3. 发布配置文件（可选）

```bash
php artisan vendor:publish --tag=xditn-config
```

这将发布以下配置文件：

- `config/xditn.php` - 核心配置
- `config/xditn_api_doc.php` - API 文档配置

## ⚙️ 配置说明

### 核心配置 (`config/xditn.php`)

```php
return [
    // 超级管理员 ID
    'super_admin' => 1,

    // 模块配置
    'module' => [
        'root' => 'modules',  // 模块根目录
        'namespace' => 'Xditn\Base\Modules',  // 命名空间
        'autoload' => env('XDITN_MODULE_AUTOLOAD', true),  // 自动加载
        'driver' => [
            'default' => 'file',  // file 或 database
            'table_name' => 'system_modules',
        ],
    ],

    // 认证配置
    'auth_model' => modules\User\Models\User::class,

    // 路由配置
    'route' => [
        'prefix' => 'api',
        'middlewares' => [
            \Xditn\Middleware\AuthMiddleware::class,
        ],
    ],

    // 开启系统接口日志
    'system_api_log' => env('XDITN_SYSTEM_API_LOG', false),
];
```

### API 文档配置 (`config/xditn_api_doc.php`)

```php
return [
    'title' => 'API 文档',
    'base_url' => env('APP_URL', 'http://localhost'),
    'routes' => [
        ['match' => ['prefixes' => ['api/*']]],
    ],
];
```

## 🚀 快速开始

### 1. 创建模块

模块会自动生成在 `modules/` 目录下：

```bash
# 创建一个博客模块
mkdir -p modules/Blog/Http/Controllers
mkdir -p modules/Blog/Models
mkdir -p modules/Blog/database/migrations
```

### 2. 创建模型

```bash
php artisan xditn:make:model Blog Post --t=posts
```

生成的模型继承自 `XditnModel`，自动包含增强功能：

```php
namespace Modules\Blog\Models;

use Xditn\Base\XditnModel;

class Post extends XditnModel
{
    protected $table = 'posts';

    protected $fillable = ['title', 'content', 'user_id'];

    // 自动支持软删除、快速搜索、字段过滤等
}
```

### 3. 创建控制器

```bash
php artisan xditn:make:controller Blog PostController
```

生成的控制器继承自 `XditnController`：

```php
namespace Modules\Blog\Http\Controllers;

use Xditn\Base\XditnController;
use Modules\Blog\Models\Post;

class PostController extends XditnController
{
    public function index()
    {
        return Post::getList();  // 自动分页、搜索、排序
    }

    public function store(Request $request)
    {
        $post = (new Post)->storeBy($request->all());
        return success($post, '创建成功');
    }
}
```

### 4. 使用增强的模型方法

```php
// 获取列表（自动分页、搜索、排序）
Post::getList();

// 创建数据
$post = (new Post)->createBy([
    'title' => '文章标题',
    'content' => '文章内容'
]);

// 更新数据
(new Post)->updateBy($id, ['title' => '新标题']);

// 批量更新
(new Post)->batchUpdate('id', [1, 2, 3], [
    'status' => [1, 1, 2]
]);

// 软删除
(new Post)->deleteBy($id);

// 恢复软删除
(new Post)->restoreBy($id);

// 树形结构
Category::getList();  // 自动返回树形结构（如果设置 $asTree = true）
```

### 5. 生成 API 文档

```bash
php artisan xditn:api:doc
```

这将在项目根目录生成 `postman.json` 文件，可直接导入 Postman。

## 📚 Artisan 命令参考

### 模块管理

```bash
# 安装模块（交互式选择）
php artisan xditn:module:install

# 强制重新安装模块
php artisan xditn:module:install --f

# 查看路由列表
php artisan xditn:route:list

# 以 JSON 格式输出路由
php artisan xditn:route:list --json
```

### 代码生成

```bash
# 创建控制器
php artisan xditn:make:controller {module} {name}

# 创建模型（自动从数据表读取字段）
php artisan xditn:make:model {module} {model} --t=table_name

# 创建事件
php artisan xditn:make:event {module} {name}

# 创建监听器（可选关联事件）
php artisan xditn:make:listener {module} {name} --event=EventName
```

### 数据库操作

```bash
# 创建迁移文件
php artisan xditn:make:migration {module} {name} {table}

# 执行模块迁移
php artisan xditn:migrate {module} --force

# 刷新模块迁移
php artisan xditn:migrate:fresh {module} --force

# 回滚模块迁移
php artisan xditn:migrate:rollback {module} --force

# 创建 Seeder
php artisan xditn:make:seeder {module} {name}

# 运行 Seeder
php artisan xditn:db:seed {module} --seeder=SeederName
```

### 其他工具

```bash
# 导出菜单为 Seeder
php artisan xditn:export:menu

# 打包应用（导出数据库结构）
php artisan xditn:build

# 查看版本
php artisan xditn:version

# 初始化运行
php artisan xditn:run
```

## 🎨 BaseOperate Trait 方法详解

所有继承自 `XditnModel` 的模型都自动拥有以下方法：

### 查询方法

| 方法              | 说明                  | 返回值                             |
| ----------------- | --------------------- | ---------------------------------- |
| `getList()`       | 获取列表（分页/树形） | `LengthAwarePaginator\|Collection` |
| `firstBy($id)`    | 根据 ID 查询单条      | `Model\|null`                      |
| `findAllBy($ids)` | 根据 ID 数组查询多条  | `Collection`                       |

### 创建/更新方法

| 方法                               | 说明                   | 返回值  |
| ---------------------------------- | ---------------------- | ------- |
| `createBy($data)`                  | 创建数据               | `Model` |
| `storeBy($data)`                   | 保存数据（更新或创建） | `Model` |
| `updateBy($id, $data)`             | 更新数据               | `bool`  |
| `batchUpdate($field, $ids, $data)` | 批量更新               | `bool`  |
| `toggleBy($id, $field)`            | 切换状态字段           | `bool`  |

### 删除方法

| 方法                  | 说明       | 返回值 |
| --------------------- | ---------- | ------ |
| `deleteBy($ids)`      | 软删除     | `bool` |
| `forceDeleteBy($ids)` | 物理删除   | `bool` |
| `restoreBy($ids)`     | 恢复软删除 | `bool` |

### 搜索方法

```php
// 快速搜索（自动处理 keyword 参数）
Post::quickSearch()->get();

// 模糊搜索
Post::whereLike('title', '关键词')->get();
```

## 🔐 权限控制

### 字段级别权限

在模型中配置字段访问权限：

```php
class User extends XditnModel
{
    protected $columnAccess = true;  // 启用字段访问控制

    // 在 permissions 表中配置字段权限
    // field: users.password, roles: [1, 2]
}
```

### 数据范围控制

```php
class Post extends XditnModel
{
    // 自动添加 created_by 字段
    protected $isFillCreatorId = true;

    // 可以基于用户 ID 限制数据访问范围
}
```

## 📝 API 响应格式

### 成功响应

```json
{
  "code": 200,
  "message": "操作成功",
  "data": {
    "id": 1,
    "title": "文章标题"
  }
}
```

### 分页响应

```json
{
    "code": 200,
    "message": "success",
    "data": {
        "data": [...],
        "current_page": 1,
        "total": 100,
        "per_page": 15
    }
}
```

### 错误响应

```json
{
  "code": 400,
  "message": "参数错误",
  "data": null
}
```

### 辅助函数

```php
// 成功响应
return success($data, '操作成功');

// 失败响应
return failed('操作失败', 400);

// 分页响应
return paginate($data);
```

## 🏗️ 项目结构

```
xditn-laravel-module/
├── config/                      # 配置文件
│   ├── xditn.php               # 核心配置
│   └── xditn_api_doc.php       # API 文档配置
├── database/
│   └── migrations/             # 数据库迁移
├── modules/                    # 模块目录（自动生成）
│   ├── User/                   # 用户模块示例
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   ├── Models/
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   └── seeders/
│   │   └── routes.php
│   └── ...
├── src/                        # 核心源码
│   ├── Base/                   # 基础类
│   │   ├── XditnController.php
│   │   ├── XditnModel.php
│   │   └── modules/            # 内置模块
│   ├── Commands/               # Artisan 命令
│   ├── Enums/                  # 枚举类
│   ├── Exceptions/             # 异常处理
│   ├── Middleware/             # 中间件
│   ├── Providers/              # 服务提供者
│   ├── Support/                # 辅助类
│   └── Traits/                 # Trait
│       └── DB/                 # 数据库增强
│           ├── BaseOperate.php
│           ├── WithAttributes.php
│           └── ...
└── vendor/                     # 依赖包
```

## 🔌 集成的第三方包

- **Laravel Sanctum** - API 认证
- **EasyWeChat** - 微信开发
- **Yansongda/Pay** - 支付集成
- **Maatwebsite/Excel** - Excel 处理
- **Knuckleswtf/Scribe** - API 文档生成
- **Laravel Pint** - 代码格式化

## 🐛 常见问题

### 1. 时间戳不自动更新？

确保模型中正确配置：

```php
public $timestamps = false;  // Xditn 使用自定义时间戳处理
protected $dateFormat = 'U';  // Unix 时间戳
```

### 2. 字段无法写入？

检查 `$fillable` 配置或使用字段过滤：

```php
// 在创建/更新时自动过滤字段
$model->createBy($data);  // 只会写入 $fillable 中的字段
```

### 3. 模块加载失败？

检查以下配置：

- `composer.json` 中的 PSR-4 自动加载配置
- `.env` 中的 `XDITN_MODULE_AUTOLOAD=true`
- 运行 `composer dump-autoload`

### 4. 权限控制不生效？

确保：

- 配置了 `super_admin` ID
- 正确配置了 `auth_model`
- 使用了 `AuthMiddleware` 中间件

## 🤝 贡献指南

### 提交规范

遵循 [Conventional Commits](https://www.conventionalcommits.org/) 规范：

- `feat:` 新功能
- `fix:` 修复 Bug
- `docs:` 文档更新
- `style:` 代码格式（不影响功能）
- `refactor:` 重构（不增加功能，不修复 Bug）
- `perf:` 性能优化
- `test:` 测试相关
- `chore:` 构建过程或辅助工具
- `revert:` 回退
- `build:` 打包

### 开发流程

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'feat: Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 提交 Pull Request

## 📄 License

本项目采用 [MIT License](LICENSE) 开源协议。

## 🙏 致谢

本项目参考并借鉴了以下优秀开源项目：

- [catch-admin/core](https://github.com/catch-admin/core) - 核心架构设计
- [Laravel Framework](https://laravel.com) - 基础框架
- [nwidart/laravel-modules](https://github.com/nwidart/laravel-modules) - 模块管理思路

## 📮 联系方式

- **作者**: lan
- **邮箱**: 532006864@qq.com
- **Issues**: [GitHub Issues](https://github.com/xditn/laravel-mmodule/issues)

---

**⭐ 如果这个项目对你有帮助，请给个 Star！**
