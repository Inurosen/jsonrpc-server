<?php


class TestValidatorSimpleInteger extends \Inurosen\JsonRPCServer\Validator
{
    public function rules(): array
    {
        return [
            \Inurosen\JsonRPCServer\Rules\RequiredRule::class,
            \Inurosen\JsonRPCServer\Rules\IntegerRule::class,
        ];
    }
}
