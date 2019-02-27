<?php


namespace Inurosen\JsonRPCServer\Rules;


class RequiredRule extends AbstractRule
{
    protected $message = 'Field is required';

    public function handle($field, $params): bool
    {
        return isset($params[$field]);
    }

}
