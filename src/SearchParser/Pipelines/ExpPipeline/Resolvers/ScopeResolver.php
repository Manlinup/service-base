<?php

namespace Sak\Core\SearchParser\Pipelines\ExpPipeline\Resolvers;

use Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases\ScopeCase;

class ScopeResolver extends AbstractResolver
{
    protected static $resolverReg = '/^\s*(?:(OR|AND)\s+)?\((.+?)\)\s*$/i';

    protected function getCaseClasses()
    {
        return ScopeCase::class;
    }
}
