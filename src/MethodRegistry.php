<?php


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
