# Cnlure Admin


## 项目介绍

Cnlure Admin 是 Cnlure 项目的管理端，基于 Laravel 框架开发。

## 如何运行项目

1. 安装依赖

```bash
composer install
```

2. 配置环境变量


3. 生成应用密钥

```bash
php artisan key:generate
```

4. 运行项目

```bash
php artisan serve
```

5. 访问项目

在浏览器中访问 `http://localhost:8000` 即可查看项目。

6. 本地连接线上数据库

在本地开发环境中，我们可以连接线上数据库进行调试。将 `.env.local_production` 文件中的数据库配置修改为线上数据库的配置，然后启动时传入环境变量

```bash
php artisan serve --env=local_production
```

