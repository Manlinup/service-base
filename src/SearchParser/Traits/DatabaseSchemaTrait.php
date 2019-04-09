<?php

namespace Sak\Core\SearchParser\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

trait DatabaseSchemaTrait
{
    public static function getSchemaData(Builder $builder, string $table)
    {
        //以schema + 数据库名+表名做个缓存key
        $cacheKey = 'schema-' . $builder->getConnection()->getDatabaseName() . '-' . $table;

        return Cache::remember($cacheKey, 60 * 24, function () use ($builder, $table) {
            $columns =$builder->getConnection()->getDoctrineSchemaManager()->listTableDetails($table)->getColumns();
            foreach ($columns as $key => $column) {
                $columns[$key] = $column->getType()->getName();
            }
            return $columns;
        });
    }
}
