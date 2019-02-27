<?php


namespace Inurosen\JsonRPCServer\Rules;


class BooleanRule extends AbstractRule
{
    protected $message = 'Field must be boolean';

    public function handle($field, $params): bool
    {
        return !isset($params[$field]) || is_bool($params[$field]);
    }
}
