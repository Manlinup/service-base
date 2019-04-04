<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Log\Formatter;

/**
 * Class LineFormatter
 * @package Sak\Core\Log\Formatter
 */
class LineFormatter extends FilterFormatter
{

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $formatter = parent::format($record);
        $output = parent::filterKeywords($formatter, $record);

        return $output;
    }
}
