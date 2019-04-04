<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Middleware;

use Closure;
use Illuminate\Support\Facades\Validator;
use Sak\Core\Exceptions\ValidationException;

/**
 * Class ValidateQueryParam
 * @package Sak\Core\Middleware
 */
class ValidateQueryParam
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $rules     = config('sak.pagination.rules', []);
        $validator = Validator::make($request->query->all(), $rules);
        //如果验证失败
        if ($validator->fails()) {
            throw new ValidationException($validator->errors()->messages());
        }

        return $next($request);
    }
}
