<?php


namespace Inurosen\JsonRPCServer\Rules;


class StringRule extends AbstractRule
{
    protected $message = 'Field must be string';

    public function handle($field, $params): bool
    {
        return !isset($params[$field]) || is_string($params[$field]);
    }
}
