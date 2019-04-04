<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Facades;

use Illuminate\Support\Facades\Log;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;

/**
 * Class CustomLog
 * @package Sak\Core\Facades
 */
class CustomLog
{
    /**
     * 默认的记录日志handler
     * @var array
     */
    public static $defaultHandlers = [];

    /**
     * 当前指定的handler
     * @var array
     */
    public static $currentHandlers = [];


    /**
     * Register a file log handler.
     *
     * @param  string $path
     * @param  string $level
     * @return void
     */
    public static function useFiles($path, $level = 'debug')
    {
        Log::useFiles($path, $level);

        self::saveCurrentHandler();
    }

    /**
     * Register a daily file log handler.
     *
     * @param  string $path
     * @param  int $days
     * @param  string $level
     * @return void
     */
    public static function useDailyFiles($path, $days = 0, $level = 'debug')
    {
        Log::useFiles($path, $days, $level);

        self::saveCurrentHandler();
    }

    /**
     * Register a Syslog handler.
     *
     * @param  string $name
     * @param  string $level
     * @param  mixed $facility
     */
    public static function useSyslog($name = 'laravel', $level = 'debug', $facility = LOG_USER)
    {
        Log::useFiles($name, $level, $facility);

        self::saveCurrentHandler();
    }

    /**
     * Register an error_log handler.
     *
     * @param  string $level
     * @param  int $messageType
     * @return void
     */
    public static function useErrorLog($level = 'debug', $messageType = ErrorLogHandler::OPERATING_SYSTEM)
    {
        Log::useFiles($level, $messageType);

        self::saveCurrentHandler();
    }

    /**
     * 保存当前日志的handler,然后移除
     */
    public static function saveCurrentHandler()
    {
        $handlers              = Log::getMonolog()->getHandlers();
        self::$currentHandlers = [current($handlers)];
        array_shift($handlers);
        self::$defaultHandlers = $handlers;
    }


    public static function __callStatic($method, $args)
    {
        $instance = new self();
        Log::getMonolog()->setHandlers(self::$currentHandlers);
        if (!method_exists($instance, $method)) {
            return tap(Log::$method(...$args), function ($value) {
                Log::getMonolog()->setHandlers(self::$defaultHandlers);
            });
        }

        return $instance->$method(...$args);
    }
}
