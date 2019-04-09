<?php

namespace Sak\Core\SearchParser\Pipelines\ExpPipeline\Middlewares;

use Illuminate\Database\Eloquent\Builder;
use Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases\Contracts\CaseInterface;
use Closure;
use Sak\Core\SearchParser\Exceptions\InvalidCaseException;
use Sak\Core\SearchParser\Traits\DatabaseSchemaTrait;

class CheckIsFieldExistedMiddleware extends AbstractMiddleware
{
    use DatabaseSchemaTrait;

    public function handle(Builder $builder, CaseInterface $case, Closure $next)
    {
        $table = $this->getTableName($builder, $case);

        $columns = array_keys(self::getSchemaData($builder, $table));

        if (!in_array($case->field, $columns)) {
            throw new InvalidCaseException(sprintf(
                "Field `%s` does not exists in table `%s`.",
                $case->field,
                $table
            ));
        }

        return $next($builder, $case);
    }

    protected function getTableName(Builder $builder, CaseInterface $case)
    {
        if ($case->fieldRelation) {
            return $case->getTable(
                $builder->getRelation($case->fieldRelation)
            );
        }

        return $case->getTable($builder);
    }
}
