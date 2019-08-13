<?php


namespace App\Exception;


use Throwable;

class InvalidModelException extends \LogicException
{

    public function __construct($message = 'Invalid model given', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
