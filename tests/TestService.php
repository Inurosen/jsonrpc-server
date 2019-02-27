<?php


class TestService
{
    public function foo()
    {
        return 'foo';
    }

    public static function bar($params)
    {
        return $params;
    }

    public function baz($params)
    {
        throw new \Exception('On noes!', 123);
    }
}
