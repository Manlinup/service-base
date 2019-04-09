<?php

namespace Sak\Core\SearchParser\Pipelines\ExpPipeline\Cases\ValueTypes;

use Doctrine\DBAL\Types\Type;

class DatetimeValueType extends AbstractValueType
{
    public static $allowedFieldTypes = [
        Type::DATE,
        Type::DATETIME,
        Type::DATETIMETZ,
        Type::TIME,
        Type::INTEGER,
        Type::BIGINT,
    ];

    public static function match($builder, $table, $field, $value = null)
    {
        $fieldType = static::getFieldType($builder, $table, $field);

        /**
         * 如果传入一个较大的时间戳 strtotime 会返回 false，
         * 此时判断是否是整形，是的话就是时间戳
         */
        if (static::checkIsFieldTypeSupported($fieldType) &&
            (strtotime($value) !== false || self::checkIsTimestamp($value))
        ) {
            return new static($fieldType, $value);
        }

        return null;
    }

    private static function checkIsTimestamp($value)
    {
        return is_numeric($value) && (string)(int)$value === $value;
    }

    public function explain()
    {
        switch ($this->fieldType) {
            case Type::DATE:
            case Type::DATETIME:
            case Type::DATETIMETZ:
                if (self::checkIsTimestamp($this->value)) {
                    return date('Y-m-d H:i:s', $this->value);
                }

                return date('Y-m-d H:i:s', strtotime($this->value));
            case Type::TIME:
            case Type::INTEGER:
            case Type::BIGINT:
                if (self::checkIsTimestamp($this->value)) {
                    return $this->value;
                }

                return (string)strtotime($this->value);
            default:
                return (string)strtotime($this->value);
        }
    }
}
