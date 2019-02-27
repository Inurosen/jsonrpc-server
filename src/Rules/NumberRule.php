<?php


namespace Inurosen\JsonRPCServer\Rules;


class NumberRule extends AbstractRule
{
    protected $message = 'Field must be a number';

    public function handle($field, $params): bool
    {
        return !isset($params[$field]) || is_numeric($params[$field]);
    }
}
