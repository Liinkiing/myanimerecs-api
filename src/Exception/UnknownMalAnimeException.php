<?php


namespace App\Exception;


use Throwable;

class UnknownMalAnimeException extends \LogicException
{

    public function __construct($message = 'Unkown MyAnimeList item. (no id found)', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
