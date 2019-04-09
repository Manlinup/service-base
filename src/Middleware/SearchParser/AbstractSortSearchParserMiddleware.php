<?php

namespace Sak\Core\Middleware\SearchParser;

use Sak\Core\SearchParser\Pipelines\OrderPipeline\Middlewares\AbstractMiddleware;
use Sak\Core\Repositories\BaseRepository;

/**
 * Class AbstractSortSearchParserMiddleware
 * @package Sak\Core\Middleware\SearchParser
 */
abstract class AbstractSortSearchParserMiddleware extends AbstractMiddleware implements MiddlewareInterface
{
    /**
     * @var BaseRepository
     */
    protected $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }
}
