<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Class CacheKeys
 * @package Sak\Core\Traits
 */
class CacheKeys
{

    /**
     * @var string
     */
    protected static $storeCacheKey = null;

    /**
     * @var array
     */
    protected static $keys = null;

    /**
     * @param $group
     * @param $key
     *
     * @return void
     */
    public static function putKey($group, $key)
    {
        self::loadKeys();
        self::$keys[$group] = self::getKeys($group);
        if (!in_array($key, self::$keys[$group])) {
            self::$keys[$group][] = $key;
        }

        self::storeKeys();
    }

    /**
     * @return array|mixed
     */
    public static function loadKeys()
    {
        if (!is_null(self::$keys) && is_array(self::$keys)) {
            return self::$keys;
        }
        $cacheKey = self::getCacheKeys();
        if (!Cache::has($cacheKey)) {
            self::storeKeys();
        }
        $content    = Cache::get($cacheKey);
        self::$keys = json_decode($content, true);

        return self::$keys;
    }

    /**
     * @return string
     */
    public static function getCacheKeys()
    {
        if (is_null(self::$storeCacheKey)) {
            self::$storeCacheKey = "repository-cache";
        }

        return self::$storeCacheKey;
    }


    public static function storeKeys()
    {
        $cacheKey   = self::getCacheKeys();
        self::$keys = is_null(self::$keys) ? [] : self::$keys;
        $content    = json_encode(self::$keys);

        return Cache::forever($cacheKey, $content);
    }

    /**
     * @param $group
     *
     * @return array|mixed
     */
    public static function getKeys($group)
    {
        self::loadKeys();
        self::$keys[$group] = isset(self::$keys[$group]) ? self::$keys[$group] : [];

        return self::$keys[$group];
    }

    /**
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new static;

        return call_user_func_array([
            $instance,
            $method
        ], $parameters);
    }

    /**
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $instance = new static;

        return call_user_func_array([
            $instance,
            $method
        ], $parameters);
    }
}
