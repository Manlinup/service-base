<?php

namespace Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases;

use Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases\Contracts\CaseInterface;
use Illuminate\Database\Eloquent\Builder;
use Closure;
use Illuminate\Http\Request;
use Sak\Core\SearchParser\SearchParser;
use Avris\Bag\Bag;
use Sak\Core\SearchParser\Exceptions;

class ScopeCase implements CaseInterface
{
    protected $value;

    /**
     * @var SearchParser $parser
     */
    protected $parser;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getMiddlewares()
    {
        $request = app('request');

        return [function (Builder $builder, CaseInterface $case, Closure $next) use ($request) {
            /**
             * 如果通过 search Header 头传递了 q 表达式
             * 则在此重置，防止因为重复检查导致的死循环
             */
            if ($request->hasHeader('search')) {
                $request->headers->set('search', null);
            }

            $request->query->set(getConfig('advancedSearch'), $this->value);
            $this->parser = new SearchParser($request);

            return $next($builder, $case);
        }];
    }

    public function getTable($builder)
    {
        return null;
    }

    public function getSupportedValueTypes()
    {
        throw new Exceptions\InvalidValueTypeException(
            "Scope case doesn't support any value types."
        );
    }

    public function assemble(Builder $builder, Bag $payload)
    {
        $this->parser->parse($builder);
    }
}
