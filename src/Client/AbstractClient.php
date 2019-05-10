<?php

namespace Sak\Core\Client;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Sak\Core\Exceptions\BadRequestException;
use Sak\Core\Exceptions\NotFoundException;
use Sak\Core\Exceptions\ServerErrorException;
use Sak\Core\Traits\HttpRequestOptions;
use Sak\Core\Traits\SignJwtTrait;

/**
 * Class AbstractClient
 * @package Sak\Core\Client
 */
abstract class AbstractClient
{
    use HttpRequestOptions, SignJwtTrait;

    /**
     * API地址
     *
     * @var null
     */
    protected $_api = null;


    /**
     * @var Client
     */
    protected $_client;

    /**
     * @var Request
     */
    public $request;

    /**
     * 最终请求的参数
     * @var array
     */
    protected $options = [];

    /**
     * 依赖请求的url
     * @var string
     */
    protected $fromUrl = '';

    /**
     * 请求微服务的资源名
     * @var string
     */
    protected $uri = '';

    /**
     * 记录日志中的分隔符，用于方便定位
     */
    const LOG_SEPARATOR = ' ########## ';

    /**
     * AbstractClient constructor.
     * @param Client $client
     * @param Request $request
     */
    public function __construct(Client $client, Request $request)
    {
        $this->_client = $client;
        $this->request = $request;
        $this->onConstruct();
    }

    /**
     * 必须要实现的方法，用于初始化api地址
     * @return mixed
     */
    abstract public function onConstruct();


    /**
     * @param $method
     * @param string $patch
     * @param array $options
     * @return AbstractClient|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $patch = '', array $options = [])
    {
        $resource_name = str_replace('/', '.', $patch);

        try {
            $url = $this->getUri($patch);

            //$options[CURLOPT_HTTP200ALIASES] = [400];
            //$options['version'] = '1.0';

            $this->fromUrl = $url;
            $this->uri = $patch;

//            $prefix = current(explode('/', $patch));
//            if (in_array($prefix, config('sak.global_jwt.service', []))) {
//                $this->setAppKey($this->signGlobal());
//            }

            //$this->options = $this->processOptions($options);
            $this->options = $options;

            config(['originating_service' => $resource_name]);

            $response = $this->_client->request($method, $url, $this->options);
            $result = $response->getBody()->getContents();

            return $result;
        } catch (ClientException $e) {   //捕获400级别的错误
            return $this->transResponse($e);
        } catch (RequestException $e) {  //在发送网络错误(连接超时、DNS错误等)时
            Log::error($e->getMessage());
            throw new ServerErrorException("An network error has occurred when request the {$resource_name} micro service.");
        } catch (ServerException $e) {  //如果 http_errors 请求参数设置成true，在500级别的错误的时候将会抛出
            Log::error($e->getMessage());
            throw new ServerErrorException("An error has occurred when request the {$resource_name} micro service.");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new ServerErrorException("An unknown error has occurred.");
        }
    }

    /**
     * 获取完整的API的地址
     *
     * @param $path
     * @return string
     */
    public function getUri($path)
    {
        $path = trim($path, '/');
        $uri = rtrim($this->_api, '/') . '/' . $path;
        return $uri;
    }

    /**
     *  转化调用的微服务之间抛出的错误信息
     * @param \Exception $exception
     * @return mixed
     */
    public function transResponse(\Exception $exception)
    {
        $requestHeader = \Illuminate\Support\Facades\Request::header();
        $content = $exception->getResponse()->getBody()->getContents();
        Log::error("The request micro service ({$this->fromUrl}) response an error:" . $content . self::LOG_SEPARATOR . "The request header:" . json_encode($this->processOptions($this->options)) . self::LOG_SEPARATOR . "The all headers:" . json_encode($requestHeader));

        $resource_name = current(explode('/', ltrim($this->uri, '/')));
        $request_error = "An error has occurred when request the {$resource_name} micro service.";

        //如果抛出的是401或者403，认为是kong抛出的鉴权错误，直接跑出500服务错误
        if (in_array($exception->getCode(), [401, 403])) {
            throw new ServerErrorException($request_error . $content);
        }
        //如果是非401、403错误，告知调用方是哪个资源微服务抛出的，其他原样返回

        //当前请求头所有的header
        //$responseHeader = $exception->getResponse()->getHeaders();
        //抛出异常，让调用方自己捕获
        throw new BadRequestException($content, 10101, $request_error);
    }
}
