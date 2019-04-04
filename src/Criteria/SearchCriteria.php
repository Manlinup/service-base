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
use SearchParser\SearchParser;
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
        $request = app('rawRequest');
        $this->request = $request;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        $this->registerSearchParserMiddlewares($repository);
        $this->registerSearchParserFunctionClass();

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

    protected function registerSearchParserFunctionClass()
    {
        /**
         * @var Repository $config
         */
        $config = app('config');
        $configPath = 'search_parser.functionClass';

        /**
         * 如果用户设置了该配置项，则不使用 service-base 提供的版本
         * 如果仍旧想使用 service-base 版本的函数，则可以继承
         */
        if (!$config->get($configPath)) {
            $config->set($configPath, SearchParserFunctions::class);
        }
    }
}
