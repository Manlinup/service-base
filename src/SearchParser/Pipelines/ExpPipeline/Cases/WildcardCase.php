<?php

namespace Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases;

use Sak\Core\SearchParser\Pipelines\ExpPipeline\Middlewares;

class WildcardCase extends AbstractCase
{
    protected static $preMiddlewares = [
        Middlewares\EscapeQuotedStringMiddleware::class,
        Middlewares\TrimSurroundQuotes::class,
    ];

    public function getSupportedValueTypes()
    {
        return [
            ValueTypes\QuotedStringValueType::class
        ];
    }

    protected function buildLaravelQuery($builder)
    {
        if ($table = $this->getTable($builder)) {
            $builder->where(
                $table . '.' . $this->field,
                strtoupper(trim($this->negation . ' LIKE')),
                $this->value
            );
        }
    }
}
