<?php

namespace Sak\Core\Middleware\SearchParser;

use Illuminate\Database\Eloquent\Builder;
use Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases\AbstractCase;
use Closure;
use Sak\Core\Criteria\BaseCriteria;
use Sak\Core\Repositories\BaseRepository;

/**
 * Class SortVirtualFieldsMiddleware
 * @package Sak\Core\Middleware\SearchParser
 */
class SortVirtualFieldsMiddleware extends AbstractSortSearchParserMiddleware
{
    public function handle(Builder $builder, $field, $direction, Closure $next)
    {
        /**
         * @var AbstractCase $case
         */
        $criteria = null;
        $virtualFields = $this->repository->getVirtualFields();

        if (is_array($virtualFields) && !empty($virtualFields)) {
            if (isset($virtualFields[$field])) {
                $criteria = $virtualFields[$field];
            }
        }

        /**
         * 虚字段处理后不再进行其他操作
         * 处理程序可以是一个 callback, Criteria 类名或者 Criteria 对象
         */
        if (isset($criteria)) {
            if ($criteria instanceof Closure) {
                return call_user_func_array($criteria, [
                    $builder, $this->repository, $field, $direction
                ]);
            }

            if (is_string($criteria)) {
                $criteria = app($criteria);
            }


            if ($criteria instanceof BaseCriteria) {
                return $criteria->processOrderByVirtualFields($builder, $this->repository, $field, $direction);
            }
        }

        return $next($builder, $field, $direction); // 如果没有找到对应的虚字段，则进行后续操作
    }

    public static function register(BaseRepository $repository, $middleware)
    {
        return function (Builder $builder, $field, $direction, Closure $next) use ($repository, $middleware) {
            return (new $middleware($repository))->handle($builder, $field, $direction, $next);
        };
    }
}
