<?php

namespace Sak\Core\Response;

use Dingo\Api\Http\Response\Factory as BaseFactory;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use Dingo\Api\Http\Response;
use Closure;
use League\Fractal\TransformerAbstract;

/**
 * Class Factory
 * @package Sak\Core\Response
 */
class Factory extends BaseFactory
{

    protected $currentTransformer;

    /**
     * @param $transformer
     */
    public function setTransformer(TransformerAbstract $transformer)
    {
        $this->currentTransformer = $transformer;
    }

    /**
     * @return mixed
     */
    public function getTransformer()
    {
        return $this->currentTransformer;
    }

    /**
     * Bind a collection to a transformer and start building a response.
     *
     * @param \Illuminate\Support\Collection $collection
     * @param object $transformer
     * @param array|\Closure $parameters
     * @param \Closure|null $after
     *
     * @return \Dingo\Api\Http\Response
     */
    public function collection(Collection $collection, $transformer = null, $parameters = [], Closure $after = null)
    {
        if (is_null($transformer)) {
            $transformer = $this->currentTransformer;
        }

        if ($collection->isEmpty()) {
            $class = get_class($collection);
        } else {
            $class = get_class($collection->first());
        }

        if ($parameters instanceof \Closure) {
            $after      = $parameters;
            $parameters = [];
        }

        $binding = $this->transformer->register($class, $transformer, $parameters, $after);

        return new Response($collection, 200, [], $binding);
    }

    /**
     * Bind an item to a transformer and start building a response.
     *
     * @param object $item
     * @param object $transformer
     * @param array $parameters
     * @param \Closure $after
     *
     * @return \Dingo\Api\Http\Response
     */
    public function item($item, $transformer = null, $parameters = [], Closure $after = null)
    {
        if (is_null($transformer)) {
            $transformer = $this->currentTransformer;
        }
        $class = get_class($item);

        if ($parameters instanceof \Closure) {
            $after      = $parameters;
            $parameters = [];
        }
        $binding = $this->transformer->register($class, $transformer, $parameters, $after);

        return new Response($item, 200, [], $binding);
    }

    /**
     * Bind a paginator to a transformer and start building a response.
     *
     * @param \Illuminate\Contracts\Pagination\Paginator $paginator
     * @param object $transformer
     * @param array $parameters
     * @param \Closure $after
     *
     * @return \Dingo\Api\Http\Response
     */
    public function paginator(Paginator $paginator, $transformer = null, array $parameters = [], Closure $after = null)
    {
        if (is_null($transformer)) {
            $transformer = $this->currentTransformer;
        }

        if ($paginator->isEmpty()) {
            $class = get_class($paginator);
        } else {
            $class = get_class($paginator->first());
        }
        $binding = $this->transformer->register($class, $transformer, $parameters, $after);

        return new Response($paginator, 200, [], $binding);
    }
}
