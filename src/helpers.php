<?php

/**
 * 递归过滤xss
 *
 * @param $array
 * @return array
 */
function xssCleanRecurse($array)
{
    $result = [];

    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result[$key] = xssCleanRecurse($value);
        } else {
            $result[$key] = xssClean($value);
        }
    }

    return $result;
}

/**
 * 包装的xss方法
 *
 * @param $value
 * @return string
 */
function xssClean($value)
{
    $value = htmlentities($value);
    //以http(s)开头的作为url，不转义&符号
    if ((stripos($value, 'http://') === 0) || (stripos($value, 'https://') === 0)) {
        $value = str_replace('&amp;', '&', $value);
    }
    return $value;
}

/**
 * 校验是不是单条记录
 *
 * @param $record
 * @return bool
 */
function checkIsSingleRecord($record)
{
    if (count($record) == count($record, 1)) {
        return true;
    }

    if (!isset($record[0])) {
        return true;
    }

    return false;
}

/**
 * 校验是否是生产环境
 * @return bool
 */
function checkIsProduction()
{
    if (in_array(app('env'), ['production'])) {
        return true;
    }
    return false;
}


/**
 * 替换脚手架自动生成目录，动态增加模块名
 * @param string $name
 */
function addModulePathForGenerator($name = '')
{
    if (empty($name)) {
        return;
    }
    $generatorFiles = config('sak.generator.paths');
    $extraFiles     = ['repository_interface', 'transformer_interface'];
    $prefix         = 'sak.generator.paths.';
    foreach ($generatorFiles as $key => $generatorFile) {
        if (in_array($key, $extraFiles)) {
            config([$prefix.$key => str_replace('/Contracts', '/'.$name.'/Contracts', $generatorFile)]);
        }
        config([$prefix.$key => $generatorFile.'/'.$name]);
    }
}

/**
 * 校验一个字符串是否是json
 * @param string $string
 * @return bool
 */
function isJson($string = '')
{
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}

/**
 * 兼容nsq pub格式，与java组统一
 * @param $data
 * @return array
 */
function createNsqPayload($data)
{
    return [
        'msg'                   => $data,
        'composite_http_header' => [
            'request_id'         => config('request_id'),
            'authorization'      => config('authorization'),
            'app_key'            => config('app_key'),
            'consumer_tenant_id' => config('tenant_id'),
            'consumer_user_id'   => config('user_id'),
        ],
    ];
}

if (! function_exists('route_path')) {
    /**
     * Get the path to the routes folder.
     *
     * @param  string  $path
     * @return string
     */
    function route_path($path = '')
    {
        return app()->basePath().DIRECTORY_SEPARATOR.'routes'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}
