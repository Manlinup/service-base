<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Dingo\Api\Contract\Debug\MessageBagErrors;
use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandler;
use Dingo\Api\Exception\Handler as DingoHandler;
use Psr\Log\LoggerInterface;

/**
 * Class Handler
 * @package Sak\Core\Exceptions
 */
class Handler extends DingoHandler implements IlluminateExceptionHandler
{

    public $handlerExceptions = [];

    /**
     * 如果是不可预期的异常，给默认的错误
     * @var string
     */
    protected $defaultMessage = 'An unexpected error occurred and we were unable to resolve it, please contact support on customer service.';

    /**
     * Prepare the replacements array by gathering the keys and values.
     *
     * @param \Exception $exception
     *
     * @return array
     */
    protected function prepareReplacements(Exception $exception)
    {
        $statusCode = $this->getStatusCode($exception);

        if (!$message = $exception->getMessage()) {
            $message = sprintf('%d %s', $statusCode, Response::$statusTexts[$statusCode]);
        }

        $replacements = [
            ':message'     => $message,
            ':status_code' => $statusCode,
        ];

        // 替换error code 错误码
        if ($code = $exception->getCode()) {
            $replacements[':code'] = $code;
        }

        // 如果是继承了dingo的MessageBagErrors方法，归类为校验错误，加入validation_failures参数
        if ($exception instanceof MessageBagErrors && $exception->hasErrors()) {
            $previous = $exception->getPrevious();
            // 如果是500错误，加入tracking_id追踪
            if ($previous instanceof ServerErrorException || $exception instanceof ServerErrorException) {
                $replacements[':tracking_id'] = config('request_id');
                $replacements[':originating_service'] = config('originating_service');
            } else {
                $replacements[':validation_failures'] = [];
                foreach ($errors = $exception->getErrors()->getMessages() as $field => $error) {
                    if (in_array($field, ['error_code', 'error_message'], true)) {
                        $msg = $errors;
                    } elseif (!is_numeric($field)) {
                        $msg = [
                            'error_message' => array_get($error, 'error_message'),
                            'field_name'    => $field,
                        ];
                        if ($errorRule = array_get($error, 'error_rule')) {
                            $msg['error_rule'] = $errorRule;
                        }
                    } else {
                        $msg['error_message'] = is_array($error) ? current($error) : $error;
                    }
                    $replacements[':validation_failures'][] = $msg;
                }
            }
        } else {  //如果不是抛出实现了MessageBagErrors，尝试从配置中查找error code码，否则就给默认-1
            $replacements[':code'] = array_get(config('api.customErrorCode'), get_class($exception), -1);
            $replacements[':message'] = $this->defaultMessage;
            $replacements[':tracking_id'] = config('request_id');
        }

        if ($this->runningInDebugMode()) {
            $replacements[':debug'] = [
                'line'  => $exception->getLine(),
                'file'  => $exception->getFile(),
                'class' => get_class($exception),
                'trace' => explode("\n", $exception->getTraceAsString()),
            ];
        }

        return array_merge($replacements, $this->replacements);
    }

    /**
     * @param Exception $exception
     */
    public function report(Exception $exception)
    {
        if (!$this->parentHandler->shouldReport($exception)) {
            return;
        }

        $key = spl_object_hash($exception);
        if (empty($this->handlerExceptions[$key])) {
            $validation = '';
            if ($exception instanceof MessageBagErrors) {
                $validation = $exception->getErrors()->__toString();
            }
            $res = $exception->getMessage() . $validation . $exception->getFile() . ':' . $exception->getLine() . '.Current exception class:' . get_class($exception);
            $logger = app(LoggerInterface::class);
            $logger->error($res);
        }
        $this->handlerExceptions[$key] = true;
    }
}
