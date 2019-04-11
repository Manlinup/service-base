<?php

namespace Sak\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Sak\Core\Exceptions\NotFoundException;

/**
 * Trait ValidateRequest
 * @package Sak\Core\Traits
 */
trait ValidateRequest
{
    protected $columns;

    public function isDefined(Request $request)
    {
        $action = $request->route()->getActionName();
        list($class, $method) = explode('@', $action);
        $reflection = new \ReflectionClass($class);
        $reflection = new \Reflection($method);
    }

    /**
     * 验证传递的参数字段中是否在数据库中存在
     * @param Model $model
     * @param $field
     */
    public function checkColumn(Model $model, $field)
    {
        $field = is_array($field) ? $field : [$field];
        foreach ($field as $item) {
            if (!$this->hasColumn($model, $item)) {
                throw new NotFoundException(["The column {$item} not found."]);
            }
        }
    }

    /**
     * 验证关联关系是否存在
     * @param Model $model
     * @param $with
     */
    public function checkRelation(Model $model, $with)
    {
        $with = (array) $with;
        foreach ($with as $item) {
            if (!method_exists($model, $item)) {
                throw new NotFoundException(["The relation method {$item} not found."]);
            }
        }
    }

    /**
     * 获取数据表中所有字段
     * @param Model $model
     * @return mixed
     */
    public function getColumns(Model $model)
    {
        $table = $model->getTable();
        if (!$this->columns[$table]) {
            $this->columns[$table] = $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());
        }

        return $this->columns[$table];
    }

    public function hasColumn(Model $model, $column)
    {
        return in_array(strtolower($column), array_map('strtolower', $this->getColumns($model)));
    }
}
