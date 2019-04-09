<?php

namespace Sak\Core\SearchParser\Supports;

use Sak\Core\SearchParser\Exceptions\NotSupportedFunctionException;

class Functions
{
    public static function date($str = null)
    {
        static $nowTime;

        if (!isset($nowTime)) {
            $nowTime = date('Y-m-d H:i:s');
        }

        $dateTime = new DateTime($nowTime);

        if (!is_string($str) && is_scalar($str)) {
            $str = (string)$str;
        }

        if (is_string($str) && !empty($str)) {
            $map = [
                'd' => 'day',
                'w' => 'weeks',
                'm' => 'month',
                'y' => 'year',
                'h' => 'hour',
                'i' => 'minute',
                's' => 'second',
            ];
            $operators = ['+', '-'];
            $segments = array_map(function ($value) {
                return trim($value);
            }, explode(',', $str));

            foreach ($segments as $segment) {
                if (!in_array($segment[0], $operators)) {
                    $segment = '+' . $segment;
                }

                if (array_has($map, $modifier = $segment[strlen($segment) - 1])) {
                    $shift = sprintf('%s %s', substr($segment, 0, -1), $map[$modifier]);
                    $dateTime->modify($shift);
                }
            }
        }

        return $dateTime->format('Y-m-d');
    }
}
