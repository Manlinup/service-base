<?php

namespace Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases;

use Sak\Core\SearchParser\Pipelines\ExpPipeline\Middlewares;

class EqualCase extends AbstractCase
{
    protected static $preMiddlewares = [
        Middlewares\EscapeQuotedStringMiddleware::class,
        Middlewares\TrimSurroundQuotes::class,
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
            $builder->where(
                $table . '.' . $this->field,
                $this->like ?
                    (strtoupper($this->negation) === 'NOT' ? 'NOT LIKE' : 'LIKE')
                    :
                    (strtoupper($this->negation) === 'NOT' ? '<>' : '='),
                $this->value
            );
        }
    }
}
