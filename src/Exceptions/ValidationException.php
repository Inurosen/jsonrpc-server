<?php
/**
 *  This file is part of JSON-RPC 2.0 Server Library
 *
 * (c) Renat Khaertdinov <inurosen@inurosen.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inurosen\JsonRPCServer\Exceptions;


use Throwable;

class ValidationException extends \Exception
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
