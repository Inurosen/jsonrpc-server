<?php


namespace Inurosen\JsonRPCServer\Rules;


class DecimalRule extends AbstractRule
{
    protected $message = 'Field must be a decimal number';

    public function handle($field, $params): bool
    {
        return !isset($params[$field]) || is_double($params[$field]);
    }
}
