<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Requests;

use Dingo\Api\Http\FormRequest;
use Sak\Core\Exceptions\AccessDeniedException;
use Sak\Core\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Dingo\Api\Http\Request;

/**
 * Class BaseRequest
 * @package Sak\Core\Requests
 */
abstract class BaseRequest extends FormRequest
{

    protected $errorCode = [];

    public function authorize()
    {
        return true;
    }


    /**
     * Get all of the input and files for the request.
     * @param null $keys
     * @return array
     */
    public function all($keys = null)
    {
        return $this->json()->all();
    }

    /**
     * 覆写掉validate，可以在验证之后再额外处理一些逻辑
     *
     * @return void
     */
    public function validate()
    {
        $this->prepareForValidation();

        $instance = $this->getValidatorInstance();

        if (! $this->passesAuthorization()) {
            $this->failedAuthorization();
        } elseif (! $instance->passes()) {
            $this->failedValidation($instance);
        }

        $this->afterForValidation();
    }

    /**
     * 验证之后处理一些逻辑
     */
    public function afterForValidation()
    {
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        $message =  $validator->errors()->getMessages();

        $customMsg = [];
        foreach ($message as $field => $msg) {
            $customMsg[$field]['error_message'] = is_array($msg) ? current($msg) : $msg;
            $customMsg[$field]['error_code'] = array_get($this->errorCode, $field, 0);
        }

        if ($this->container['request'] instanceof Request) {
            throw new ValidationException($customMsg);
        }

        parent::failedValidation($validator);
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     */
    protected function failedAuthorization()
    {
        if ($this->container['request'] instanceof Request) {
            throw new AccessDeniedException();
        }

        parent::failedAuthorization();
    }
}
