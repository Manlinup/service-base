<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Sak\Core\Facades\CustomLog;

/**
 * Class QueryListener
 * @package Sak\Core\Listeners
 */
class QueryListener
{
    /**
     * QueryListener constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  QueryExecuted $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        if (!checkIsProduction() && $event->sql && config('sak.dumpSqlLog', false)) {
            // 把sql  的日志独立分开
            $fileName = storage_path('logs/sql-' . date('Y-m-d') . '.log');
            CustomLog::useFiles($fileName, 'info');

            $sql = str_replace("?", "'%s'", $event->sql);
            $log = config('request_id') . " Sql: " . vsprintf($sql, $event->bindings) . " Time: " . $event->time;

            CustomLog::info($log);
        }
    }
}
