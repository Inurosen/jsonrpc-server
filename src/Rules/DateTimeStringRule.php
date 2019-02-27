<?php


namespace Inurosen\JsonRPCServer\Rules;


class DateTimeStringRule extends AbstractRule
{
    protected $message = 'Field must be a datetime string of Y-m-d H:i:s format';

    public function handle($field, $params): bool
    {
        return !isset($params[$field]) || preg_match('/^(\d{4}(?:\-\d{2}){2} \d{2}(?:\:\d{2}){2})$/',
                $params[$field]) !== 0;
    }
}
