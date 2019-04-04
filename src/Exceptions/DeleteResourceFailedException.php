<?php

namespace Sak\Core\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;
use Dingo\Api\Contract\Debug\MessageBagErrors;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class DeleteResourceFailedException
 * @package Sak\Core\Exceptions
 */
class DeleteResourceFailedException extends HttpException implements MessageBagErrors
{
    /**
     * MessageBag errors.
     *
     * @var \Illuminate\Support\MessageBag
     */
    protected $errors;

    /**
     * Create a new resource exception instance.
     *
     * @param string                               $message
     * @param \Illuminate\Support\MessageBag|array $errors
     * @param \Exception                           $previous
     * @param array                                $headers
     * @param int                                  $code
     *
     * @return void
     */
    public function __construct(
        $errors = null,
        $code = 10101,
        $message = null,
        Exception $previous = null,
        $headers = []
    ) {

        $this->errors = new MessageBag((array)$errors);

        parent::__construct(400, $message, $previous, $headers, $code);
    }

    /**
     * Get the errors message bag.
     *
     * @return \Illuminate\Support\MessageBag
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Determine if message bag has any errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return ! $this->errors->isEmpty();
    }
}
