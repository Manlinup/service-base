<?php

namespace Sak\Core\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Http\Request;
use Sak\Core\Criteria\SearchCriteria;
use Mews\Purifier\Facades\Purifier;
use Mews\Purifier\PurifierServiceProvider as BasePurifierServiceProvider;
use Symfony\Component\HttpFoundation\ParameterBag;

class PurifierServiceProvider extends BasePurifierServiceProvider
{

    /**
     * Boot the service provider.
     *
     * @return null
     */
    public function boot()
    {
        $this->setupConfig();
    }

    /**
     * Setup the config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__ . '/../../resources/config/purifier.php');
        if ($this->app->runningInConsole()) {
            $this->publishes([$source => config_path('purifier.php')]);
        }
        $this->mergeConfigFrom($source, 'purifier');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        AliasLoader::getInstance()->alias('Purifier', Purifier::class);

        $rawRequest = clone $this->app->request;
        $rawRequest->setJson(new ParameterBag($rawRequest->request->all()));

        $this->app->singleton('rawRequest', function ($app) use ($rawRequest) {
            return $rawRequest;
        });

        $this->app->when(SearchCriteria::class)
            ->needs(Request::class)
            ->give(function () use ($rawRequest) {
                return $rawRequest;
            });
    }
}
