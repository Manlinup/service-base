<?php

namespace Sak\Core\SearchParser\Pipelines\ExpPipeline\Middlewares;

use Illuminate\Database\Eloquent\Builder;
use Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases\Contracts\CaseInterface;
use Closure;

abstract class AbstractMiddleware
{
    abstract public function handle(Builder $builder, CaseInterface $case, Closure $next);
}
