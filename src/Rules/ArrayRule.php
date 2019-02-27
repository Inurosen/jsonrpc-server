<?php


namespace Inurosen\JsonRPCServer\Rules;


class ArrayRule extends AbstractRule
{
    protected $message = 'Field must be array';

    public function handle($field, $params): bool
    {
        return !isset($params[$field]) || is_array($params[$field]);
    }
}
