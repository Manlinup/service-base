<?php

namespace Sak\Core\Middleware\SearchParser;

use Illuminate\Database\Eloquent\Builder;
use Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases\AbstractCase;
use Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases\Contracts\CaseInterface;
use Closure;
use Sak\Core\Criteria\BaseCriteria;
use Sak\Core\Repositories\BaseRepository;

/**
 * Class QueryVirtualFieldsMiddleware
 * @package Sak\Core\Middleware\SearchParser
 */
class QueryVirtualFieldsMiddleware extends AbstractQuerySearchParserMiddleware
{
    public function handle(Builder $builder, CaseInterface $case, Closure $next)
    {
        /**
         * @var AbstractCase $case
         */
        $criteria = null;
        $virtualFields = $this->repository->getVirtualFields();

        if (is_array($virtualFields) && !empty($virtualFields)) {
            if (isset($virtualFields[$case->field])) {
                $criteria = $virtualFields[$case->field];
            }
        }

        /**
         * 虚字段处理后不再进行其他操作
         * 处理程序可以是一个 callback, Criteria 类名或者 Criteria 对象
         */
        if (isset($criteria)) {
            if ($criteria instanceof Closure) {
                return call_user_func_array($criteria, [$builder, $this->repository, $case]);
            }

            if (is_string($criteria)) {
                $criteria = app($criteria);
            }

            if ($criteria instanceof BaseCriteria) {
                return $criteria->processVirtualFields($builder, $this->repository, $case);
            }
        }

        return $next($builder, $case); // 如果没有找到对应的虚字段，则进行后续操作
    }

    public static function register(BaseRepository $repository, $middleware)
    {
        return function (Builder $builder, CaseInterface $case, Closure $next) use ($repository, $middleware) {
            return (new $middleware($repository))->handle($builder, $case, $next);
        };
    }
}
