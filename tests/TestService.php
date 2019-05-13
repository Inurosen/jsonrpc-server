<?php
/**
 *  This file is part of JSON-RPC 2.0 Server Library
 *
 * (c) Renat Khaertdinov <inurosen@inurosen.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TestService
{
    public static function bar($params)
    {
        return $params;
    }

    public function foo()
    {
        return 'foo';
    }

    public function scope()
    {
        return 'test';
    }

    public function baz($params)
    {
        throw new \Exception('On noes!', 123);
    }
}
