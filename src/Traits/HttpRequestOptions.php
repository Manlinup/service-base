<?php

namespace Sak\Core\Traits;

use Sak\Core\Exceptions\ValidationException;

/**
 * Trait HttpRequestOptions
 * @package Sak\Core\Traits
 */
trait HttpRequestOptions
{

    /**
     * http请求header头,这边默认写死，可以动态配置
     * @var string
     */
    private $accept = null;


    /**
     * 用户id
     * @var null
     */
    private $user_id = null;

    /**
     * app_key用于服务内部调用服务，过kong的鉴权用的
     * @var
     */
    private $app_key = null;

    /**
     * 请求request id 唯一值
     * @var null
     */
    private $request_id = null;


    /**
     * jwt token
     * @var null
     */
    private $authorization = null;

    /**
     * 前端请求用的，用于请求权限
     * @var null
     */
    private $api_token = null;



    /**
     * 获取header头
     * @return string
     */
    protected function getAccept()
    {
        if (empty($this->accept)) {
            $standTree    = config('api.standardsTree', 'vnd');
            $subtype      = config('api.subtype', 'sak');
            $version      = config('api.version', 'v1');
            $this->accept = 'application/' . $standTree . '.' . $subtype . '.' . $version . '+json';
        }

        return $this->accept;
    }

    /**
     * @param $value
     */
    public function setAccept($value)
    {
        $this->accept = $value;
    }

    /**
     * @return null
     */
    public function getApi()
    {
        return $this->_api;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->_client;
    }


    /**
     * @return mixed|null
     */
    public function getUserId()
    {
        return empty($this->user_id) ? config('user_id') : $this->user_id;
    }

    /**
     * @param $value
     */
    public function setUserId($value)
    {
        $this->user_id = $value;
    }

    /**
     * @return mixed|null
     */
    public function getAppKey()
    {
        return empty($this->app_key) ? config('app_key') : $this->app_key;
    }

    /**
     * @param $value
     */
    public function setAppKey($value)
    {
        $this->app_key = $value;
    }

    /**
     * @return mixed|null
     */
    public function getRequestId()
    {
        return empty($this->request_id) ? config('request_id') : $this->request_id;
    }

    /**
     * @param $value
     */
    public function setRequestId($value)
    {
        $this->request_id = $value;
    }


    /**
     * @return mixed|null
     */
    public function getAuthorization()
    {
        return empty($this->authorization) ? config('authorization') : $this->authorization;
    }

    /**
     * @param $value
     */
    public function setAuthorization($value)
    {
        $this->authorization = $value;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function __set($key, $value)
    {
        try {
            $this->{$key} = $value;
            return $this;
        } catch (\Exception $e) {
            throw new ValidationException("Unknown setter {$key}.");
        }
    }

    /**
     * @TODO  后期打开 Authorization
     * 处理微服务之间通过kong调用需要的header头信息
     * @param array $options
     * @return array
     */
    public function processOptions(array $options)
    {
        $headers = [
            //config('api.jwtHeaderKey', 'Authorization')              => $this->getAuthorization(),
            config('api.apiHeaderKey', 'accept')                     => $this->getAccept(),
            config('api.headerRequestIdKey', 'X-Request-ID')         => $this->getRequestId(),
            config('api.headerUserIdKey', 'X-Consumer-User-ID')      => $this->getUserId(),
            config('api.headerAppKey', 'X-App-Key')                  => $this->getAppKey(),
        ];
        $options['headers'] = array_merge($headers, isset($options['headers']) ? $options['headers'] : []);

        return $options;
    }
}
