<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Listeners;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Sak\Core\Events\UpdateRepositoryCache;
use Sak\Core\Traits\CacheKeys;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class CleanCacheRepository
 * @package Prettus\Repository\Listeners
 */
class CleanCacheRepository
{

    /**
     * @var CacheRepository
     */
    protected $cache = null;

    /**
     * @var RepositoryInterface
     */
    protected $repository = null;

    /**
     * @var Model
     */
    protected $model = null;


    /**
     *
     */
    public function __construct()
    {
        $this->cache = app(config('repository.cache.repository', 'cache'));
    }

    /**
     * @param UpdateRepositoryCache $event
     */
    public function handle(UpdateRepositoryCache $event)
    {
        try {
            $cleanEnabled = config("repository.cache.clean.enabled", true);
            if ($cleanEnabled) {
                $this->repository = $event->getRepository();
                $this->model = $event->getModel();

                $cacheKeys = CacheKeys::getKeys(get_class($this->repository));
                if (is_array($cacheKeys)) {
                    foreach ($cacheKeys as $key) {
                        $this->cache->forget($key);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
