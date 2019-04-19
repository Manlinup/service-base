# Lumen日志处理

> 需要依赖"ramsey/uuid": "~3.6"

### 1. 在bootstrap/app.php中添加如下代码块
``` php
<?php

//命令行下使用需要添加
$app->configure('app');

$app->configureMonologUsing(function (Monolog\Logger $monoLog) use ($app) {
    $configureLogging = new Inno\Core\Log\ConfigureLogging();

    return $configureLogging->configureHandlers($app, $monoLog);
});
```

### 2.在config目录下添加app.php
``` php
<?php

return [

    //支持Single,Daily,Syslog
    'log'           => env('LOG_TYPE', 'Daily'),

    /**
     * 应用程序名称
     */
    'app_name'      => env('APP_NAME', 'lumen'),

    /**
     * 日志位置
     */
    'log_path'      => env('LOG_PATH', ''),

    /**
     * 日志文件名称
     */
    'log_name'      => env('APP_NAME', 'lumen'),

    /**
     * 日志文件最大数
     */
    'log_max_files' => env('LOG_DAY', 30),
];
```

### 3.日志使用
日志工具提供定义在 RFC 5424 的七个级别：debug、info、notice、warning、error、critical 和 alert。
```php
<?php

use Log;
Log::info('Log message', ['context' => 'Other helpful information']);

//使用RequestId
//加载RequestIdMiddleware

//在不加载RequestIdMiddleware情况下在命令行下使用RequestId
\Sak\Core\Log\LogProcessor::setRequestId(); //设置
\Sak\Core\Log\LogProcessor::getRequestId(); //获取
```
