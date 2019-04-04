<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Traits;

use Illuminate\Events\CallQueuedListener;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

trait AssignHeaderTrait
{

    /**
     * 命令行或者队列中丢失header头，重新赋值
     * @param array $headers
     */
    public function setConfigHeader(array $headers)
    {
        try {
            //header头经过http请求后会全部转为小写
            $userKey      = strtolower(config('api.headerUserIdKey', 'X-Consumer-User-ID'));
            $requestIdKey = strtolower(config('api.headerRequestIdKey', 'X-Request-ID'));

            $userId    = $this->getHeader($headers, $userKey);
            $requestId = $this->getHeader($headers, $requestIdKey);

            config(['app_key' => config('api.appKey')]);
            config(['user_id' => $userId]);
            config(['request_id' => $requestId]);
        } catch (\Throwable $e) {
            Log::error("传递的header头有误，请检查:" . $e->getMessage());
        }
    }


    /**
     * 判断是否存在key值，如果有就返回，没有就全部默认为-1
     * @param array $headers
     * @param string $key
     * @return string
     */
    protected function getHeader(array $headers, string $key): string
    {
        if (isset($headers[$key])) {
            return !is_array($headers[$key]) ? $headers[$key] : current($headers[$key]);
        }
        Log::error("特别注意！！！由于传递到队列中没有{$key}头，所以全部默认为-1.");

        return "-1";
    }
}
