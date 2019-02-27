<?php


namespace Inurosen\JsonRPCServer\Exceptions;


use Throwable;

class ServerErrorException extends \Exception
{
    private $context;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null, $context = [])
    {
        $this->context = $context;
        parent::__construct($message, $code, $previous);
    }

    public function getContext()
    {
        return $this->context;
    }
}
