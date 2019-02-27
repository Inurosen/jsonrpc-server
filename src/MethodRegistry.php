<?php
/**
 *  This file is part of JSON-RPC 2.0 Server Library
 *
 * (c) Renat Khaertdinov <inurosen@inurosen.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inurosen\JsonRPCServer;


class MethodRegistry
{
    private static $methods = [];

    private function __construct()
    {
    }

    public static function register($method, $handler, $validator = null)
    {
        if (!isset(static::$methods[$method])) {
            static::$methods[$method] = [
                'handler'   => $handler,
                'validator' => $validator,
            ];
        }
    }

    public static function reset()
    {
        static::$methods = [];
    }

    public static function getMethods()
    {
        return static::$methods;
    }
}
