<?php

namespace Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases;

use Sak\Core\SearchParser\Pipelines\ExpPipeline\Middlewares;

class GreatCase extends AbstractCase
{
    protected static $preMiddlewares = [
        Middlewares\TrimSurroundQuotes::class,
    ];

    public function getSupportedValueTypes()
    {
        return [
            ValueTypes\NumericValueType::class,
            ValueTypes\DatetimeValueType::class
        ];
    }

    protected function buildLaravelQuery($builder)
    {
        if ($table = $this->getTable($builder)) {
            $builder->where(
                $table . '.' . $this->field,
                strtoupper($this->negation) === 'NOT' ? '<=' : '>',
                $this->value
            );
        }
    }
}
