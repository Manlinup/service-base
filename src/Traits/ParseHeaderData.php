<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Trait ParseHeaderData
 * @package Sak\Core\Traits
 */
trait ParseHeaderData
{
    /**
     * 当前请求的jwt token
     * @var
     */
    public $jwt;

    /**
     * 当前请求的用户id
     * @var
     */
    public $user_id;

    /**
     * 当前请求的用户组织id
     * @var
     */
    public $organization_id;

    /**
     * 当前请求的用户角色id
     * @var
     */
    public $profile_id;


    /**
     * 与签发的系统jwt token，用于服务与服务之内内部调用
     * @var
     */
    public $app_key;

    /**
     * 资源访问权限验证，暂时不做处理
     * @var
     */
    public $api_token;

    protected $user;

    protected $userRoles;

    public function bootParseHeaderData()
    {
        $this->jwt             = Request::header(config('api.jwtHeaderKey', 'Authorization'));
        $this->user_id         = Request::header(config('api.headerUserIdKey', 'X-Consumer-User-ID'));
        $this->app_key         = Request::header(config('api.headerAppKey', 'X-App-Key'));
        $this->api_token       = Request::header(config('api.headerApiTokenKey', 'X-API-Token'));
        $this->organization_id = Request::header(config('api.headerOrganizationIDKey', 'X-Consumer-Organization-ID'));
        $this->profile_id      = Request::header(config('api.headerProfileIDKey', 'X-Consumer-Profile-ID'));

        Request::instance()->request->add(['token' => $this->jwt]);
        $this->setHeaderConfig();
    }


    public function setHeaderConfig()
    {
        $this->setAppKey();
        $this->setJwt();
        $this->setUserId();
        $this->setOrganizationId();
        $this->setProfileId();
    }

    /**
     * 如果app key不存在，尝试从环境变量读取
     * @param string $app_key
     * @return \Illuminate\Config\Repository|mixed
     */
    public function setAppKey($app_key = '')
    {
        if (!empty($app_key)) {
            $this->app_key = $app_key;
        } else {
            $this->app_key = $this->app_key ?: config('api.appKey');
        }
        config(['app_key' => $this->app_key]);
    }

    /**
     * @param string $jwt
     */
    public function setJwt($jwt = '')
    {
        if (!empty($jwt)) {
            $this->jwt = $jwt;
            config(['authorization' => $jwt]);
        } else {
            config(['authorization' => $this->jwt]);
        }
    }


    /**
     * 设置当前用户id
     * @param string $user_id
     */
    public function setUserId($user_id = '')
    {
        try {
            if (!empty($user_id)) {
                $this->user_id = $user_id;
            } else {
                $this->user_id = $this->user_id ?: $this->getUserByJwt()->id;
            }
            config(['user_id' => $this->user_id]);
        } catch (\Exception $e) {
            config(['user_id' => null]);
        }
    }

    /**
     * 设置当前用户组织id
     * @param string $organization_id
     */
    public function setOrganizationId($organization_id = '')
    {
        try {
            if (!empty($organization_id)) {
                $this->organization_id = $organization_id;
            } else {
                $this->organization_id = $this->organization_id ?: $this->getUserByJwt()->organization_id;
            }
            config(['organization_id' => $this->organization_id]);
        } catch (\Exception $e) {
            //如果没有可能是从shell过来了，设置为null
            config(['organization_id' => null]);
        }
    }

    /**
     * 设置当前用户角色id
     * @param string $profile_id
     */
    public function setProfileId($profile_id = '')
    {
        try {
            if (!empty($profile_id)) {
                $this->profile_id = $profile_id;
            } else {
                $this->profile_id = $this->profile_id ?: $this->getUserRolesByJwt();
            }
            config(['profile_id' => $this->profile_id]);
        } catch (\Exception $e) {
            //如果没有可能是从shell过来了，设置为null
            config(['profile_id' => null]);
        }
    }


    /**
     * 解析jwt token字段
     * @return mixed
     */
    protected function parseBaseJwt()
    {
        try {
            return collect(explode('.', $this->jwt))->map(function ($value, $key) {
                return ($key == 1) ? json_decode(base64_decode($value)) : $value;
            });
        } catch (\Exception $e) {
            Log::error("jwt token is invalid.");
            return false;
        }
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    private function getUserByJwt()
    {
        if ($this->user) {
            return $this->user;
        }

        return Cache::tags('authorization_user')->get($this->jwt, function () {
            return $this->guard()->user();
        });
    }

    /**
     * @return mixed
     */
    private function getUserRolesByJwt()
    {

        if ($this->userRoles) {
            return $this->userRoles;
        }

        return Cache::tags('authorization_user_roles')->get($this->jwt, []);
    }
}
