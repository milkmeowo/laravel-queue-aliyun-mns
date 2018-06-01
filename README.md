# Laravel-MNS

Laravel 队列的阿里云消息服务（MNS）驱动。

[![StyleCI PSR2](https://github.styleci.io/repos/135667835/shield)](https://github.styleci.io/repos/135667835)
[![Build Status](https://www.travis-ci.org/milkmeowo/laravel-queue-aliyun-mns.svg?branch=master)](https://www.travis-ci.org/milkmeowo/laravel-queue-aliyun-mns)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/milkmeowo/laravel-queue-aliyun-mns/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/milkmeowo/laravel-queue-aliyun-mns/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/milkmeowo/laravel-queue-aliyun-mns/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/milkmeowo/laravel-queue-aliyun-mns/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/milkmeowo/laravel-mns/v/stable)](https://packagist.org/packages/milkmeowo/laravel-mns)
[![Total Downloads](https://poser.pugx.org/milkmeowo/laravel-mns/downloads)](https://packagist.org/packages/milkmeowo/laravel-mns)
[![Latest Unstable Version](https://poser.pugx.org/milkmeowo/laravel-mns/v/unstable)](https://packagist.org/packages/milkmeowo/laravel-mns)
[![License](https://poser.pugx.org/milkmeowo/laravel-mns/license)](https://packagist.org/packages/milkmeowo/laravel-mns)

## 安装

```bash
composer require milkmeowo/laravel-mns
```

## 配置

1.在 config/app.php 注册 ServiceProvider(Laravel 5.5 无需手动注册)

```php
'providers' => [
       // ...
   Milkmeowo\LaravelMns\LaravelMnsServiceProvider::class,
],
```
   
2.在 `config/queue.php` 中增加 `mns` 配置：

```php
'connections' => [
    'redis' => [
        'driver'     => 'redis',
        'connection' => 'default',
        'queue'      => 'default',
        'expire'     => 60,
    ],
    // 新增阿里云 MNS。
    'mns'   => [
       'driver'       => 'mns',
       'key'          => env('QUEUE_MNS_ACCESS_KEY', ''),
       'secret'       => env('QUEUE_MNS_SECRET_KEY', ''),
       'endpoint'     => env('QUEUE_MNS_ENDPOINT', ''),
       'queue'        => env('QUEUE_NAME',''),
       'wait_seconds' => env('QUEUE_WAIT_SECONDS', 30),
   ],
],
```

3.在 `.env` 增加

```bash
QUEUE_DRIVER=mns
QUEUE_NAME=your_queue_name
QUEUE_MNS_ACCESS_KEY=your_acccess_key
QUEUE_MNS_SECRET_KEY=your_secret_key
QUEUE_MNS_ENDPOINT=your-endpoint
# 关于 wait_seconds 可以看 https://help.aliyun.com/document_detail/35136.html
QUEUE_WAIT_SECONDS=30
```

## 使用

正常使用 Laravel Queue 即可：

* [Laravel 队列服务（官方英文文档）](https://laravel.com/docs/5.6/queues)

* [Laravel 队列服务（中文文档）](https://laravel-china.org/docs/laravel/5.6/queues/1395)

## 命令

### 列出所有队列

```bash
php artisan queue:mns:list 
// 例如
php artisan queue:mns:list
// 输入队列名以 prefix 开头的队列
php artisan queue:mns:list -p

# 请填写prefix:
# >

```

### 增加队列

```bash
php artisan queue:mns:create 队列名
// 例如
php artisan queue:mns:create wechat-notify
```

### 删除队列

```bash
php artisan queue:mns:delete 队列名
// 例如
php artisan queue:mns:delete wechat-notify
```

### 显示队列内容

```bash
php artisan queue:mns:show 队列名
// 例如
php artisan queue:mns:show wechat-notify
```

### 删除队列所有内容

```bash
php artisan queue:mns:flush 队列名
// 例如
php artisan queue:mns:flush wechat-notify
```

## 测试

``` bash
$ composer test
```

## 参考

- [abrahamgreyson/laravel-mns](https://github.com/abrahamgreyson/laravel-mns)

## 许可

MIT
