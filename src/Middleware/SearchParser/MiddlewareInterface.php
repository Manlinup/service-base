<?php

namespace Sak\Core\Middleware\SearchParser;

use Sak\Core\Repositories\BaseRepository;

/**
 * Interface MiddlewareInterface
 * @package Sak\Core\Middleware\SearchParser
 */
interface MiddlewareInterface
{
    public static function register(BaseRepository $repository, $middleware);
}
