<?php

namespace Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases;

use Sak\Core\SearchParser\Pipelines\ExpPipeline\Middlewares;

class SetCase extends AbstractCase
{
    protected static $preMiddlewares = [
        Middlewares\SplitMultipleValueMiddleware::class,
    ];

    public function getSupportedValueTypes()
    {
        return [
            ValueTypes\NumericValueType::class,
            ValueTypes\DatetimeValueType::class,
            ValueTypes\QuotedStringValueType::class
        ];
    }

    protected function buildLaravelQuery($builder)
    {
        if ($table = $this->getTable($builder)) {
            call_user_func_array([
                $builder,
                strtoupper($this->negation) === 'NOT' ? 'WhereNotIn' : 'whereIn'
            ], [
                $table . '.' . $this->field,
                $this->value
            ]);
        }
    }
}
