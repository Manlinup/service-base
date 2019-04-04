<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Log\Formatter;

use Monolog\Formatter\LineFormatter;

class FilterFormatter extends LineFormatter
{

    /**
     * 邮箱正则表达式
     */
    const EMAIL_REG = "/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/";

    /**
     * 手机号正则
     */
    const CN_MOBILE_REG = "/((13[0-9])|(15[^4])|(18[0,2,3,5-9])|(17[0-8])|(147))\d{8}/";

    /**
     * 关键字
     */
    const SENSITIVE_KEYS = ["password", "passwd", "access-key", "secret-key", "\"pwd\"", "'pwd'"];

    /**
     * 手机号和邮箱替换值
     */
    const MASK_MSG = '** SENSITIVE_INFO **';

    /**
     * 关键字替换值
     */
    const WARNING_MSG = '** SENSITIVE_INFO ** This log contains following sensitive keywords: %s, please report this issue to the service maintainer.';


    /**
     * 替换日志中的邮箱和手机号
     * @param string $formatter
     * @param array $record
     * @return string
     */
    protected function filterKeywords(string $formatter, array $record)
    {
        // 替换关键字,如果有关键字直接返回，不匹配邮箱
        foreach (self::SENSITIVE_KEYS as $keyword) {
            if (stripos($formatter, $keyword)) {
                // 切割出格式前缀
                $type = $record['channel'] . '.' . $record['level_name'] . ':';
                preg_match('/^.*' . $type . '/', $formatter, $prefix);

                return current($prefix) . sprintf(self::WARNING_MSG, $keyword);
            }
        }

        // 过滤邮件和手机号
        foreach ([self::EMAIL_REG, self::CN_MOBILE_REG] as $preg) {
            $formatter = preg_replace($preg, self::MASK_MSG, $formatter);
        }

        return $formatter;
    }
}
