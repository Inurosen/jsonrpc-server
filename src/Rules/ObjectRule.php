<?php


namespace Inurosen\JsonRPCServer\Rules;


class ObjectRule extends AbstractRule
{
    protected $message = 'Field must be object';

    public function handle($field, $params): bool
    {
        return !isset($params[$field]) || is_object($params[$field]);
    }
}
