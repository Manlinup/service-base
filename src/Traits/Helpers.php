<?php

namespace Sak\Core\Traits;

use Dingo\Api\Routing\Helpers as DingoHelpers;
use Sak\Core\Response\Factory;
use Sak\Core\Transformers\BaseTransformer;
use Illuminate\Console\DetectsApplicationNamespace;

/**
 * Trait Helpers
 * @package Sak\Core\Traits
 */
trait Helpers
{
    use DingoHelpers, DetectsApplicationNamespace;

    /**
     * Get the response factory instance.
     * @return \Illuminate\Foundation\Application|mixed
     */
    protected function response()
    {
        $factory        = app(Factory::class);
        $controllerName = class_basename($this);
        $basePath       = $this->getAppNamespace() . 'Transformers\\';
        $name           = str_replace('Controller', '', $controllerName);
        $transformer    = $basePath . $name . 'Transformer';
        if (!class_exists($transformer)) {
            $baseNameSpace = explode('\\', substr(get_class($this), 0, -1 - strlen(class_basename(get_class($this)))));
            $transformer   = $this->getAppNamespace() . 'Transformers\\' . end($baseNameSpace) . '\\' . $name . 'Transformer';
        }
        if (!class_exists($transformer)) {
            $transformer = BaseTransformer::class;
        }
        $factory->setTransformer(app($transformer));

        return $factory;
    }
}
