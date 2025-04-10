# Laravel-mmdoule

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

laravel多模块管理脚本

## 简介

开发模块管理脚本，可以快速创建、删除、修改模块，同时支持模块间数据关联。


### 安装命令

```bash
composer require xditn/larave-mmodule
```

## 功能

- 初始化命令 php artisan xditn:install
- 安装模块 xditn:module:install{--f}
- 路由命令 xditn:route:list {--app : 显示应用路由列表} {--json : 以JSON格式输出路由列表}
- 控制器生成命令 xditn:make:controller {module} {name}
- 事件生成命令 xditn:make:event {module} {name}
- 监听器生成命令 xditn:make:listener {module} {name} {--event= : 自动生成关联的事件类}
- 创建模型命令类 xditn:make:model {module} {model} {--t= : the model of table name} 该类用于通过命令行创建模型文件，基于给定的模块和模型名。
- 刷新数据库迁移命令 xditn:migrate:fresh {module} {--force}
- 生成数据库迁移命令 xditn:make:migration {module : 模块名称} {name : 迁移文件名称} {table : 数据库表名}
- 执行数据库迁移命令 xditn:migrate {module : 模块名称} {--force}
- 数据库迁移回滚命令 xditn:migrate:rollback {module : 模块名称} {--force}
- 生成 Seeder 类命令 xditn:make:seeder {module : 模块名称} {name : Seeder 类名称}
- 运行数据库填充命令 xditn:db:seed {module : 模块名称} {--seeder= : 指定 Seeder 类}
- API 文档生成命令 xditn:api:doc
  {--config=xditn_api_doc : 选择使用哪个配置文件, 默认为 config/xditn_api_doc.php }
  {--no-vitepress : 不生成 VitePress API 文档 }
  {--no-postman-json : 不生成 Postman Json 文件 }
- 导出菜单命令类 xditn:export:menu {--p : 是否使用树形结构}

## 环境要求

- PHP >= 8.2
- Composer
- Laravel>= 11


## 提交规范:

- feat 新功能 feature
- fix 修复 bug
- docs 文档注释
- style 代码格式(不影响代码运行的变动)
- refactor 重构、优化(既不增加新功能，也不是修复bug)
- perf 性能优化
- test 增加测试
- chore 构建过程或辅助工具的变动
- revert 回退
- build 打包
- close 关闭 issue


## 🙏 感谢
本仓库大量参考了 [catch-admin/core](https://github.com/catch-admin/core) 的实现