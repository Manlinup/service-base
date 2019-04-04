<?php

namespace Sak\Core\Services;

use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Sak\Core\Exceptions\NotFoundException;
use Sak\Core\Exceptions\ValidationException;
use Sak\Core\Exceptions\DeleteResourceFailedException;
use Sak\Core\Exceptions\ServerErrorException;
use Prettus\Repository\Helpers\CacheKeys;
use Illuminate\Support\MessageBag;

/**
 * Class BaseService
 * @package Sak\Core\Services
 */
abstract class BaseService
{
    /**
     * 最大重插次数
     *
     * @var int $maxRedoTimes
     */
    protected static $maxRedoTimes = 1;

    /**
     * 已经加载的服务
     *
     * @var array $maxRedoTimes
     */
    protected static $loadedServices = [];

    /**
     * 已经加载的 Repository
     *
     * @var array $loadedRepositories
     */
    protected static $loadedRepositories = [];

    /**
     * 当前的repository
     * @var
     */
    protected $repository;

    /**
     * 取得一个service实例
     * @param $service
     * @return \Illuminate\Foundation\Application|mixed
     */
    public function getService($service)
    {
        if (isset(self::$loadedServices[$service])) {
            return self::$loadedServices[$service];
        }
        $serviceInstance = app($service);
        self::$loadedServices[] = $serviceInstance;

        return $serviceInstance;
    }

    /**
     * 取得一个repository实例
     * @param $repository
     * @return \Illuminate\Foundation\Application|mixed
     */
    public function getRepository($repository = null)
    {
        if (!$repository) {
            return $this->repository;
        }
        if (isset(self::$loadedRepositories[$repository])) {
            return self::$loadedRepositories[$repository];
        }
        $repositoryInstance         = app($repository);
        self::$loadedRepositories[] = $repositoryInstance;

        return $repositoryInstance;
    }

    /**
     * 封装过的获取错误信息的方法
     * 支持 l5-repository 以及 Dingo 抛出的异常
     *
     * @param \Exception $e
     * @return array
     */
    public function getErrors(\Exception $e)
    {
        $errors = [];
        if (method_exists($e, 'getMessageBag')) {
            $errors = $e->getMessageBag();
        } elseif (method_exists($e, 'getErrors')) {
            $errors = $e->getErrors();
        } elseif (method_exists($e, 'getMessage')) {
            $errors = [$e->getMessage()];
        }

        if ($errors instanceof MessageBag) {
            $errors = $errors->toArray();
        }

        return $errors;
    }

    /**
     * 获取当前资料的列表，默认取配置的分页数
     * @return mixed
     */
    public function all()
    {
        return $this->repository->all();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $item = $this->repository->findWithoutFail($id);
        if (empty($item)) {
            throw new NotFoundException([sprintf('The id:(%s) does not exists!', $id)]);
        }

        return $item;
    }

    /**
     * @param $attribute
     * @return mixed
     * @throws \Exception
     */
    public function store($attribute)
    {
        try {
            if (empty($attribute) || !is_array($attribute)) {
                throw new ValidationException(
                    ['Invalid attribute data.']
                );
            }
            $this->flushCache();
            return $this->repository->create($attribute);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new StoreResourceFailedException(
                'Could not create new entity.',
                $this->getErrors($e),
                $e
            );
        }
    }

    /**
     * @param $attribute
     * @param $id
     * @return mixed
     */
    public function update($attribute, $id)
    {
        $item = $this->repository->findWithoutFail($id);
        if (empty($item)) {
            throw new NotFoundException([sprintf('The id:(%s) item does not exists!', $id)]);
        }

        try {
            if (empty($attribute) || !is_array($attribute)) {
                throw new ValidationException(
                    ['Invalid attribute data.']
                );
            }
            $this->flushCache();
            return $this->repository->update($attribute, $id);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new UpdateResourceFailedException(
                "Could not update the id:({$id}) item.",
                $this->getErrors($e),
                $e
            );
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        if (empty($this->repository->findWithoutFail($id))) {
            throw new NotFoundException([sprintf('The id:(%s) item does not exists!', $id)]);
        }
        try {
            $result = $this->repository->delete($id);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new ServerErrorException(sprintf('Failed to delete the id:(%s) item.', $id));
        }
        $this->flushCache();

        return $result;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function destroyAll(Request $request)
    {
        if (!($ids = $request->input('ids', []))) {
            throw new ValidationException(['Invalid param ids.']);
        }
        try {
            $result = $this->repository->deleteWhereIn($ids);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new ServerErrorException(sprintf('Failed to delete the id:(%s) item.', implode(',', $ids)));
        }
        if ($result === 0) {
            throw new DeleteResourceFailedException(sprintf('Failed to delete the id:(%s) item.', implode(',', $ids)));
        }
        $this->flushCache();

        return $result;
    }

    /**
     * @param array|null $where
     * @return mixed
     */
    public function count(array $where = null)
    {
        return $this->repository->count($where);
    }

    /**
     * 清空缓存
     */
    public function flushCache()
    {
        $cacheKeys = CacheKeys::getKeys(get_class($this->repository));
        if (is_array($cacheKeys)) {
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
        }
    }
}
