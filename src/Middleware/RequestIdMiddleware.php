<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Middleware;

use Closure;
use Ramsey\Uuid\Uuid;

/**
 * Class RequestIdMiddleware
 * @package Sak\Core\Middleware
 */
class RequestIdMiddleware
{

    public function handle($request, Closure $next)
    {
        if (!isset($_SERVER['HTTP_X_REQUEST_ID'])) {
            if ($request->header('HTTP_X_REQUEST_ID')) {
                $_SERVER['HTTP_X_REQUEST_ID'] = $request->headers->get('HTTP_X_REQUEST_ID');
            } else {
                $_SERVER['HTTP_X_REQUEST_ID'] = Uuid::uuid1()->toString();
            }
            $request->server->set('HTTP_X_REQUEST_ID', $_SERVER['HTTP_X_REQUEST_ID']);
        }
        //保存request_id
        config(['request_id' => $_SERVER['HTTP_X_REQUEST_ID']]);

        $response = $next($request);
        $response->headers->set('HTTP_X_REQUEST_ID', $_SERVER['HTTP_X_REQUEST_ID'], false);

        return $response;
    }
}
