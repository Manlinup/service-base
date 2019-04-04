<?php

namespace Sak\Core\Repositories;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Container as Application;
use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Eloquent\BaseRepository as OriginBaseRepository;
use Sak\Core\Events\UpdateRepositoryCache;
use Sak\Core\Criteria\SearchCriteria;
use Illuminate\Support\Collection;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use Sak\Core\Traits\CacheableRepository;

/**
 * Class BaseRepository
 * @package Sak\Core\Repositories
 */
abstract class BaseRepository extends OriginBaseRepository implements CacheableInterface
{
    use CacheableRepository;

    protected $searchable = false;

    protected $cacheSkip = false;

    protected $virtualFields = [];

    protected $searchBlacklist = [];

    /**
     * BaseRepository constructor.
     * @param Application $app
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->criteria = new Collection();
        $this->makeModel();
        $this->makeValidator();
        $this->boot();
    }

    public function boot()
    {
        parent::boot();
        $this->pushDefaultCriteria();
        //这里检验缓存机制
    }

    public function pushDefaultCriteria()
    {
    }

    protected function searchable()
    {
        return $this->searchable;
    }

    public function getSearchBlacklist()
    {
        return $this->searchBlacklist;
    }

    public function getVirtualFields()
    {
        return $this->virtualFields;
    }


    public function beginTransaction()
    {
        DB::beginTransaction();
    }

    public function rollback()
    {
        DB::rollBack();
    }

    public function commit()
    {
        DB::commit();
    }

    public function transaction(Closure $transaction)
    {
        return DB::transaction($transaction);
    }

    /**
     * 覆写了base方法后，cache无效，因为是通过trait来覆写all方法实现cache的
     * @param array $columns
     * @return mixed
     * @throws \Illuminate\Container\EntryNotFoundException
     */
    public function all($columns = ['*'])
    {
        if (!$this->allowedCache('all') || $this->isSkippedCache()) {
            return $this->originalAll($columns);
        }
        $params = array_merge(func_get_args(), ['tenant_id' => config('tenant_id')]);
        $key = $this->getCacheKey('all', $params);
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()->remember($key, $minutes, function () use ($columns) {
            return $this->originalAll($columns);
        });

        return $value;
    }


    /**
     * 这里是为了兼容缓存方法单独提取出来
     * @param array $columns
     * @return mixed
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    protected function originalAll($columns = ['*'])
    {
        if ($this->searchable()) {
            $this->pushCriteria(app(SearchCriteria::class));
        }
        $this->applyCriteria();
        $this->applyScope();
        if ($this->model instanceof Builder) {
            $query = $this->model->getQuery();
            if (property_exists($query, 'limit') && isset($query->limit) &&
                property_exists($query, 'offset') && isset($query->offset)
            ) {
                $page = $query->offset / $query->limit + 1;
                $results = $this->model->paginate($query->limit, ['*'], 'rows', $page);
            } else {
                $results = $this->model->get($columns);
            }
        } else {
            $results = $this->model->all($columns);
        }
        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($results);
    }


    protected function applyCriteria()
    {
        if ($this->skipCriteria === true) {
            return $this;
        }
        $criteria = $this->getCriteria();
        if ($criteria) {
            foreach ($criteria as $c) {
                if ($c instanceof CriteriaInterface) {
                    $this->model = $c->apply($this->model, $this);
                }
            }
        }

        return $this;
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     * @throws \Illuminate\Container\EntryNotFoundException
     */
    public function find($id, $columns = ['*'])
    {
        if (!$this->allowedCache('find') || $this->isSkippedCache()) {
            return $this->originalFind($id, $columns);
        }
        $params = array_merge(func_get_args(), ['tenant_id' => config('tenant_id')]);
        $key = $this->getCacheKey('find', $params);
        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()->remember($key, $minutes, function () use ($id, $columns) {
            return $this->originalFind($id, $columns);
        });

        return $value;
    }

    /**
     * 为了兼容cache，单独封装出去
     * @param $id
     * @param array $columns
     * @return mixed
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function originalFind($id, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->find($id, $columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    public function delete($id)
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = false;
        if ($model = $this->model->find($id)) {
            $originalModel = clone $model;
        }
        $this->resetModel();
        if ($model instanceof Model && isset($originalModel) && $originalModel instanceof Model) {
            $result = $model->delete();
            event(new UpdateRepositoryCache($this, $originalModel));
        }

        return $this->parserResult($result);
    }

    public function deleteWhere(array $where)
    {
        $this->applyScope();

        $this->applyConditions($where);

        $deleted = $this->model->delete();

        event(new UpdateRepositoryCache($this, $this->model->getModel()));

        $this->resetModel();

        return $this->parserResult($deleted);
    }

    public function count(array $where = null)
    {
        $this->applyCriteria();
        $this->applyScope();

        if (is_array($where) && !empty($where)) {
            $this->applyConditions($where);
        }

        $count = $this->model->count();
        $this->resetModel();

        return $this->parserResult($count);
    }

    /*************************************************************************************************************/
    /*******************************       以下是覆写l5的部分方法，去除Presenter等逻辑       **************************/
    /*************************************************************************************************************/

    /**
     * Retrieve first data of repository, or return new Entity
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function firstOrNew(array $attributes = [])
    {
        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->firstOrNew($attributes);

        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Retrieve first data of repository, or create new Entity
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function firstOrCreate(array $attributes = [])
    {
        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->firstOrCreate($attributes);

        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Save a new entity in repository
     *
     * @throws ValidatorException
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes)
    {
        if (!is_null($this->validator)) {
            // we should pass data that has been casts by the model
            // to make sure data type are same because validator may need to use
            // this data to compare with data that fetch from database.
            if ($this->versionCompare($this->app->version(), "5.2.*", ">")) {
                $attributes = $this->model->newInstance()->forceFill($attributes)->makeVisible($this->model->getHidden())->toArray();
            } else {
                $model = $this->model->newInstance()->forceFill($attributes);
                $model->addVisible($this->model->getHidden());
                $attributes = $model->toArray();
            }

            $this->validator->with($attributes)->passesOrFail(ValidatorInterface::RULE_CREATE);
        }

        $model = $this->model->newInstance($attributes);
        $model->save();
        $this->resetModel();

        event(new UpdateRepositoryCache($this, $model));

        return $this->parserResult($model);
    }

    /**
     * Update a entity in repository by id
     *
     * @throws ValidatorException
     *
     * @param array $attributes
     * @param       $id
     *
     * @return mixed
     */
    public function update(array $attributes, $id)
    {
        $this->applyScope();

        if (!is_null($this->validator)) {
            // we should pass data that has been casts by the model
            // to make sure data type are same because validator may need to use
            // this data to compare with data that fetch from database.
            $attributes = $this->model->newInstance()->forceFill($attributes)->makeVisible($this->model->getHidden())->toArray();

            $this->validator->with($attributes)->setId($id)->passesOrFail(ValidatorInterface::RULE_UPDATE);
        }

        $model = $this->model->findOrFail($id);
        $model->fill($attributes);
        $model->save();

        $this->resetModel();

        event(new UpdateRepositoryCache($this, $model));

        return $this->parserResult($model);
    }

    /**
     * Update or Create an entity in repository
     *
     * @throws ValidatorException
     *
     * @param array $attributes
     * @param array $values
     *
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $this->applyScope();

        if (!is_null($this->validator)) {
            $this->validator->with($attributes)->passesOrFail(ValidatorInterface::RULE_UPDATE);
        }


        $model = $this->model->updateOrCreate($attributes, $values);

        $this->resetModel();

        event(new UpdateRepositoryCache($this, $model));

        return $this->parserResult($model);
    }

    /**
     * 根据where条件判断更新还是新增，where条件不做任何处理，仅仅用于判断
     *
     * @throws ValidatorException
     *
     * @param array $where
     * @param array $values
     *
     * @return mixed
     */
    public function updateOrCreateByWhere(array $where, array $values = [])
    {
        $this->applyScope();

        $model = tap($this->firstOrNewByWhere($where), function ($instance) use ($values) {
            $instance->fill($values)->save();
        });

        $this->resetModel();

        event(new UpdateRepositoryCache($this, $model));

        return $this->parserResult($model);
    }

    /**
     * 根据传递过来的where条件来判断是查找还是新增，where条件不做任何处理，仅仅用于判断
     *
     * @param  array $where
     * @param  array $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrNewByWhere(array $where, array $values = [])
    {
        if (!is_null($instance = $this->model->where($where)->first())) {
            return $instance;
        }

        return $this->model->newModelInstance($values);
    }


    /**
     * @param mixed $result
     * @return mixed
     */
    public function parserResult($result)
    {
        return $result;
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed|null
     */
    public function findWithoutFail($id, $columns = ['*'])
    {
        try {
            return $this->find($id, $columns);
        } catch (\Exception $e) {
            // do nothing
        }
    }


    /**
     * Find data by multiple values in one field
     *
     * @param       $field
     * @param array $values
     *
     * @return mixed
     */
    public function deleteWhereIn(array $values, $field = 'id')
    {
        $this->applyScope();

        $deleted = $this->model->whereIn($field, $values)->delete();

        event(new UpdateRepositoryCache($this, $this->model->getModel()));

        $this->resetModel();

        return $this->parserResult($deleted);
    }

    /**
     * Retrieve all data of repository, paginated
     *
     * @param null $limit
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*'])
    {
        if (!$this->allowedCache('paginate') || $this->isSkippedCache()) {
            return $this->originalPaginate($limit, $columns);
        }
        $params = array_merge(func_get_args(), ['tenant_id' => config('tenant_id')]);
        $key = $this->getCacheKey('paginate', $params);

        $minutes = $this->getCacheMinutes();
        $value = $this->getCacheRepository()->remember($key, $minutes, function () use ($limit, $columns) {
            return $this->originalPaginate($limit, $columns);
        });

        return $value;
    }


    /**
     * 为了兼容cache，单独封装出去
     *
     * @param null $limit
     * @param array $columns
     * @param string $method
     *
     * @return mixed
     */
    public function originalPaginate($limit = null, $columns = ['*'], $method = "paginate")
    {
        if ($this->searchable()) {
            $this->pushCriteria(app(SearchCriteria::class));
        }
        $this->applyCriteria();
        $this->applyScope();
        $limit = is_null($limit) ? config('inno.pagination.limit', 15) : $limit;
        $results = $this->model->{$method}($limit, $columns);
        $results->appends(app('request')->query());
        $this->resetModel();
        return $this->parserResult($results);
    }

    /**
     * 获取当前model
     * @param bool $applyCriteria
     * @return Model
     */
    public function getModel($applyCriteria = false)
    {
        $applyCriteria && $this->applyCriteria();
        return $this->model;
    }

    /**
     * 批量更新
     * @param array $where
     * @param array $attributes
     * @return mixed
     */
    public function updateWhere(array $where, array $attributes)
    {
        $this->applyScope();

        $model = $this->model->where($where)->update($attributes);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * 批量更新
     * @param $field
     * @param array $values
     * @param array $attributes
     * @return mixed
     */
    public function updateWhereIn($field, array $values, array $attributes)
    {
        $this->applyScope();

        $model = $this->model->whereIn($field, $values)->update($attributes);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * 获取支持高级检索的model
     * @return Model
     */
    public function getSearchModel()
    {
        $this->pushCriteria(app(SearchCriteria::class));
        $this->applyCriteria();
        $this->applyScope();
        $result = $this->model;
        $this->resetModel();
        $this->resetScope();

        return $result;
    }
}
