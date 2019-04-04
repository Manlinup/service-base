<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Middleware;

use Closure;
use Sak\Core\Traits\ParseHeaderData;

/**
 * Class ParseHeaderMiddleware
 * @package Sak\Core\Middleware
 */
class ParseHeaderMiddleware
{
    use ParseHeaderData;

    /**
     * 哪些路由不需要
     *
     * @var array
     */
    protected $except = [];

    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //从配置项中读取白名单
        $this->except = (array)config('api.exceptBuildConnection', []);
        //health check的路由白名单动态加入进来
        $this->except[] = config('healthCheckUri');
        if (!$this->inExceptArray($request)) {
            //初始化header验证
            $this->bootParseHeaderData();
        }
        if (trim($request->getPathInfo(), '/') == trim(config('healthCheckUri'), '/')) {
            config(['api.strict' => false]);
        }

        return $next($request);
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }
            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
