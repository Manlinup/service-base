<?php

namespace Sak\Core\SearchParser\Pipelines\ExpPipeline\Resolvers;

use Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases\LowerEqualCase;

class LowerEqualResolver extends AbstractResolver
{
    protected static $resolverReg = '/^\s*(?:(OR|AND)\s+)?(?:(\w+)\.)?(\w+)\s*<=\s*(?:(NOT)\s+)?(\-?\d+(?:\.\d+)?|".*?")\s*$/i';

    protected function getCaseClasses()
    {
        return LowerEqualCase::class;
    }
}
