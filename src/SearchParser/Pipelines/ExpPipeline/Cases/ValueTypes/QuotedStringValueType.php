<?php

namespace Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases\ValueTypes;

use Doctrine\DBAL\Types\Type;

class QuotedStringValueType extends AbstractValueType
{
    public static $allowedFieldTypes = [
        Type::STRING,
        Type::TEXT,
        Type::GUID,
        Type::TARRAY,
        Type::SIMPLE_ARRAY,
        Type::JSON_ARRAY,
        Type::JSON,
    ];

    public static function match($builder, $table, $field, $value = null)
    {
        $fieldType = static::getFieldType($builder, $table, $field);

        if (static::checkIsFieldTypeSupported($fieldType) && is_scalar($value)) {
            return new static($fieldType, $value);
        }

        return null;
    }

    public function explain()
    {
        return $this->value;
    }
}
