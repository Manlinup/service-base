<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core;

use Dingo\Api\Provider\DingoServiceProvider;
use Dingo\Api\Provider\LaravelServiceProvider;
use Sak\Core\Commands\ApiGeneratorCommand;
use Sak\Core\Commands\RollbackGeneratorCommand;
use Sak\Core\Commands\SakLaravooleCommand;
use Sak\Core\Log\ConfigureLogging;
use Sak\Core\Exceptions\Handler;
use Sak\Core\Middleware\HtmlPurifierMiddleware;
use Sak\Core\Middleware\ParseHeaderMiddleware;
use Sak\Core\Middleware\RequestIdMiddleware;
use Sak\Core\Middleware\ValidateQueryParam;
use Illuminate\Contracts\Http\Kernel;
use Sak\Core\Providers\EventServiceProvider;
use Sak\Core\Providers\PurifierServiceProvider;
use Sak\Core\Traits\AssignHeaderTrait;

/**
 * Class SakBaseServiceProvider
 * @package Sak\Core
 */
class SakBaseServiceProvider extends LaravelServiceProvider
{

    use AssignHeaderTrait;

    const VERSION = '1.0.0';

    /**
     * @var
     * 需要注入的providers
     */
    protected $providers = [
        EventServiceProvider::class,
        PurifierServiceProvider::class,
    ];

    /**
     * @var
     * 前置中间件
     */
    protected $beforeMiddleware = [
        ParseHeaderMiddleware::class,
        HtmlPurifierMiddleware::class,
    ];

    /**
     * @var
     * 后置中间件
     */
    protected $afterMiddleware = [
        ValidateQueryParam::class,
        RequestIdMiddleware::class,
    ];

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Sak\Core\Events\UpdateRepositoryCache' => [
            'Sak\Core\Listeners\CleanCacheRepository'
        ]
    ];


    public function boot()
    {
        $this->setMiddleware();
        //parent::boot();
        $this->setPublishes();
        $this->bindListeners();
        $this->customValidator();
        $this->setLog();
    }

    public function register()
    {
        parent::register();
        $this->setProviders();
        $this->commands(ApiGeneratorCommand::class);
        $this->commands(RollbackGeneratorCommand::class);
        //$this->commands(SakLaravooleCommand::class);
    }


    /**
     * Register the exception handler.
     *
     * @return void
     */
    protected function registerExceptionHandler()
    {
        $this->app->singleton('api.exception', function ($app) {
            return new Handler($app['Illuminate\Contracts\Debug\ExceptionHandler'], $this->config('errorFormat'), $this->config('debug'));
        });
    }


    /**
     * set provider
     */
    protected function setProviders()
    {
        foreach ($this->providers as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * remove provider
     * @param $name
     */
    protected function removeProvider($name)
    {
        foreach ($this->providers as $index => $provider) {
            if ($name == $provider) {
                unset($this->providers[$index]);
                break;
            }
        }
    }

    /**
     * remove middleware
     * @param $name
     * @param bool $before
     */
    protected function removeMiddleware($name, $before = true)
    {
        if ($before) {
            foreach ($this->beforeMiddleware as $index => $middleware) {
                if ($name == $middleware) {
                    unset($this->beforeMiddleware[$index]);
                    break;
                }
            }
        } else {
            foreach ($this->afterMiddleware as $index => $middleware) {
                if ($name == $middleware) {
                    unset($this->afterMiddleware[$index]);
                    break;
                }
            }
        }
    }

    /**
     * 设置config发布路径
     */
    protected function setPublishes()
    {
        $configPath  = __DIR__ . '/../resources/config/';
        $swaggerPath = __DIR__ . '/../resources/views/vendor/l5-swagger/index.blade.php';
        $routesPath  = __DIR__ . '/../resources/routes/';

        //remove dingo api publish
        self::$publishes[static::class] = [];
        $this->publishes([
            $configPath . 'sak.php'    => config_path('sak.php'),
            $configPath . 'api.php'    => config_path('api.php'),
            $routesPath . 'swagger_web.php'    => route_path('swagger_web.php'),
            $swaggerPath => resource_path('views/vendor/l5-swagger/index.blade.php')
        ]);

        $this->mergeConfigFrom($configPath . 'sak.php', 'sak');
        $this->mergeConfigFrom($configPath . 'api.php', 'api');
        $this->loadRoutesFrom($routesPath . 'swagger_web.php', 'swagger_web');
        $this->loadViewsFrom($swaggerPath, 'l5-swagger');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'sak');
    }

    /**
     * 动态更改中间件
     */
    protected function setMiddleware()
    {
        /**
         * 前置中间件
         */
        foreach ($this->beforeMiddleware as $item) {
            $this->app[Kernel::class]->prependMiddleware($item);
        }
        /**
         * 后置中间件
         */
        foreach ($this->afterMiddleware as $item) {
            $this->app[Kernel::class]->pushMiddleware($item);
        }

        $this->addMiddlewareAlias('api.validate', ValidateQueryParam::class);
    }

    /**
     * 设置自定义日志格式
     */
    protected function setLog()
    {
        $app = $this->app;
        $app->configureMonologUsing(function ($monoLog) use ($app) {
            $configureLogging = new ConfigureLogging();

            return $configureLogging->configureHandlers($app, $monoLog);
        });
    }

    /**
     * 自定义验证
     */
    public function customValidator()
    {
        //当filter或者sort中有关联字段的情况下，with参数为必填
        $this->app['validator']->extend('check_relation', function ($attribute, $value, $parameters, $validator) {
            //如果规则中有.关联关系
            if (strstr($value, '.') !== false) {
                //如果关联关系字段不存在该关联关系
                if (!$this->app->request->has($parameters)) {
                    return false;
                }
            }
            return true;
        });
        $this->app['validator']->replacer('check_relation', function ($message, $attribute, $rule, $parameters) {
            return 'The ' . $attribute . ' has relation, the ' . current($parameters) . ' must has the same relation too.';
        });
        //验证是否是合法的json
        $this->app['validator']->extend('valid_json', function ($attributes, $value, $parameters, $validation) {

            return isJson($value);
        });

        $this->app['validator']->replacer('valid_json', function ($message, $attribute, $rule, $parameters) {
            return 'The ' . $attribute . ' is not a JSON string.';
        });
    }

    /**
     * Register the application's event listeners.
     *
     * @return void
     */
    protected function bindListeners()
    {
        $events = app('events');
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
    }
}
