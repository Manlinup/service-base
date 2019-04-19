<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Log;

use Sak\Core\Log\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Illuminate\Foundation\Application;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;

/**
 * Class ConfigureLogging
 * @package Sak\Core\Log
 */
class ConfigureLogging
{

    protected $config;
    protected $defaultLogPath;
    protected $defaultLogName;
    protected $monoLog;
    protected $handler;
    protected $logPath;

    /**
     * 设置应用的Monolog处理程序
     * @param Application $app
     * @param Logger $monoLog
     * @return mixed
     */
    public function configureHandlers(Application $app, Logger $monoLog)
    {
        $type = config('sak.log') ? config('sak.log') : 'syslog';
        if (!in_array($type, ["single", "daily", "syslog", "errorlog"])) {
            $type = 'syslog';
        }
        $method = 'configure' . ucfirst($type) . 'Handler';

        $this->config         = $app->make('config');
        $this->defaultLogPath = $app->storagePath() . '/logs/';
        $this->defaultLogName = 'laravel';
        $this->monoLog        = $monoLog;

        if ($this->config->get('app.log_path')) {
            $this->logPath = rtrim($this->config->get('app.log_path'), '/') . '/';
        } else {
            $this->logPath = $this->defaultLogPath;
        }

        return $this->{$method}();
    }

    /**
     * 设置应用single模式下的Monolog处理程序
     *
     * @return mixed
     */
    protected function configureSingleHandler()
    {
        $path          = $this->logPath . $this->config->get('app.log_name', $this->defaultLogName) . '.log';
        $this->handler = new StreamHandler($path);

        return $this->pushProcessorHandler();
    }

    /**
     * 设置应用daily模式下的Monolog处理程序
     *
     * @return mixed
     */
    protected function configureDailyHandler()
    {
        $path          = $this->logPath . $this->config->get('app.log_name', $this->defaultLogName) . '.log';
        $this->handler = new RotatingFileHandler($path, $this->config->get('app.log_max_files', 5));

        return $this->pushProcessorHandler();
    }

    /**
     * 设置应用syslog模式下的Monolog处理程序
     *
     * @return mixed
     */
    protected function configureSyslogHandler()
    {
        $this->handler = new SyslogHandler($this->config->get('app.app_name', $this->defaultLogName));

        return $this->pushProcessorHandler();
    }

    /**
     * 注册Processor与Handler
     *
     * @return mixed
     */
    protected function pushProcessorHandler()
    {
        $this->handler->setFormatter($this->getDefaultFormatter());
        return $this->monoLog->pushProcessor(new LogProcessor($_SERVER))->pushHandler($this->handler);
    }

    /**
     * 设置一个默认的Monolog formatter实例
     * @return LineFormatter
     */
    protected function getDefaultFormatter()
    {
        $strFormat = "APP | %datetime% RID_%HTTP_X_REQUEST_ID% %channel%.%level_name%: %message% %context% %extra%\n";
        return new LineFormatter($strFormat, null, true, true);
    }
}
