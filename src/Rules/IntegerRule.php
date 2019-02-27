<?php


namespace Inurosen\JsonRPCServer\Rules;


class IntegerRule extends AbstractRule
{
    protected $message = 'Field must be integer';

    public function handle($field, $params): bool
    {
        return !isset($params[$field]) || is_int($params[$field]);
    }
}
