<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Criteria;

use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Sak\Core\Middleware\SearchParser as Middlewares;
use Sak\Core\SearchParser\SearchParser;
use Prettus\Repository\Contracts\RepositoryInterface;

class SearchCriteria extends BaseCriteria
{
    /**
     * @var Request
     */
    protected $request;

    protected $middlewares = [
        'advancedSearch' => [
            Middlewares\QueryVirtualFieldsMiddleware::class,
        ],
        'sort'           => [
            Middlewares\SortVirtualFieldsMiddleware::class,
        ]
    ];

    public function __construct()
    {
        $request = app('request');
        $this->request = $request;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        $this->registerSearchParserMiddlewares($repository);

        $searchParse = new SearchParser($this->request);

        /**
         * @var \Illuminate\Database\Eloquent\Model $model
         */
        return $searchParse->parse($model->newQuery());
    }

    protected function registerSearchParserMiddlewares(RepositoryInterface $repository)
    {
        /**
         * @var Repository $config
         */
        $config = app('config');
        $configPrefix = 'search_parser.middlewares';

        foreach ($this->middlewares as $category => $categoryMiddleware) {
            $categoryMiddleware = array_reverse((array)$categoryMiddleware);

            foreach ($categoryMiddleware as $middleware) {
                $configPath = sprintf('%s.%s', $configPrefix, $category);

                if (!$config->has($configPath) || !is_array($config->get($configPath))) {
                    $config->set($configPath, []);
                }

                $config->prepend(
                    $configPath,
                    $this->registerSearchParserMiddlewareCarry($repository, $middleware)
                );
            }
        }
    }

    protected function registerSearchParserMiddlewareCarry(RepositoryInterface $repository, $middleware)
    {
        return forward_static_call([$middleware, 'register'], $repository, $middleware);
    }
}
