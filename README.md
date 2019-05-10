## 赛奥科PHP 基础组件库
本项目使用[Laravel5.5](https://laravel.com/docs/5.5) 开发.

## Environment
- Nginx 1.8+
- PHP 7.1+
- MySQL 5.7+
- Redis 3.0+

## 同步到本地Composer仓库
（http://packages.sak.org/）
- 登录136 内部服务器
- 执行命令
```
cd /var/www/laradock
docker-compose exec workspace bash
cd satis
php bin/satis build config.json web
```
