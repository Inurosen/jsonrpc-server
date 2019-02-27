<?php


class TestValidatorStructureInteger extends \Inurosen\JsonRPCServer\Validator
{
    public function rules(): array
    {
        return [
            'id' => [
                \Inurosen\JsonRPCServer\Rules\RequiredRule::class,
                \Inurosen\JsonRPCServer\Rules\IntegerRule::class,
            ],
        ];
    }
}
