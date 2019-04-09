<?php

namespace Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases\ValueTypes;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;
use Sak\Core\SearchParser\Traits\DatabaseSchemaTrait;

abstract class AbstractValueType
{

    use DatabaseSchemaTrait;

    protected $fieldType;
    protected $value;

    public static $allowedFieldTypes = [
    ];

    public function __construct($fieldType, $value)
    {
        $this->fieldType = $fieldType;
        $this->value     = $value;
    }

    /**
     * @param Builder|Relation $builder
     * @param $table
     * @param $field
     * @param null $value
     * @return static|null
     */
    public static function match($builder, $table, $field, $value = null)
    {
        return null;
    }

    /**
     * @param Builder|Relation $builder
     * @param $table
     * @param $field
     * @return string
     */
    public static function getFieldType($builder, $table, $field)
    {

        $schema = self::getSchemaData($builder, $table);

        return array_get($schema, $field);
    }

    public static function checkIsFieldTypeSupported($fieldType)
    {
        return in_array($fieldType, static::$allowedFieldTypes);
    }

    abstract public function explain();
}
