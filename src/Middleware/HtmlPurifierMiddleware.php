<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class HtmlPurifierMiddleware
 * @package Sak\Core\Middleware
 */
class HtmlPurifierMiddleware
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

        $request->setJson(new ParameterBag($this->cleanJson($request->json()->all())));
        $request->query->replace(clean($request->query->all()));

        return $next($request);
    }

    /**
     * 递归过滤
     * @param array $attributes
     * @return array
     */
    protected function cleanJson(array $attributes)
    {
        foreach ($attributes as $key => $attribute) {
            if (is_array($attribute)) {
                $this->cleanJson($attribute);
            }
            if (is_string($attribute)) {
                $attribute = clean($attribute);
            }
            $attributes[$key] = $attribute;
        }

        return $attributes;
    }
}
