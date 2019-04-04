<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class EventServiceProvider
 * @package Sak\Core\Providers
 */
class EventServiceProvider extends ServiceProvider
{

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Prettus\Repository\Events\RepositoryEntityCreated' => [
            'Prettus\Repository\Listeners\CleanCacheRepository'
        ],
        'Prettus\Repository\Events\RepositoryEntityUpdated' => [
            'Prettus\Repository\Listeners\CleanCacheRepository'
        ],
        'Prettus\Repository\Events\RepositoryEntityDeleted' => [
            'Prettus\Repository\Listeners\CleanCacheRepository'
        ],
        'Illuminate\Database\Events\QueryExecuted' => [
            'Sak\Core\Listeners\QueryListener',
        ],
    ];

    /**
     * Register the application's event listeners.
     *
     * @return void
     */
    public function boot()
    {
        $events = app('events');

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        //
    }

    /**
     * Get the events and handlers.
     *
     * @return array
     */
    public function listens()
    {
        return $this->listen;
    }
}
