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


use Inurosen\JsonRPCServer\Exceptions\InvalidScopeException;

class MethodRegistry
{
    private static $methods = [];

    private function __construct()
    {
    }

    public static function register($method, $handler, $validator = null, $scope = JsonRPCService::SCOPE_DEFAULT)
    {
        if (!isset(static::$methods[$scope])) {
            static::$methods[$scope] = [];
        }

        if (!isset(static::$methods[$scope][$method])) {
            static::$methods[$scope][$method] = [
                'handler'   => $handler,
                'validator' => $validator,
            ];
        }
    }

    public static function reset($scope = JsonRPCService::SCOPE_DEFAULT)
    {
        if (!isset(static::$methods[$scope])) {
            throw new InvalidScopeException('Invalid scope: ' . $scope);
        }

        static::$methods[$scope] = [];
    }

    public static function getMethods($scope = JsonRPCService::SCOPE_DEFAULT)
    {
        if (!isset(static::$methods[$scope])) {
            throw new InvalidScopeException('Invalid scope: ' . $scope);
        }

        return static::$methods[$scope];
    }
}
