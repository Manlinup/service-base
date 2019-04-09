<?php

namespace Sak\Core\Middleware\SearchParser;

use Sak\Core\SearchParser\Pipelines\ExpPipeline\Middlewares\AbstractMiddleware;
use Sak\Core\Repositories\BaseRepository;

/**
 * Class AbstractQuerySearchParserMiddleware
 * @package Sak\Core\Middleware\SearchParser
 */
abstract class AbstractQuerySearchParserMiddleware extends AbstractMiddleware implements MiddlewareInterface
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
