<?php


namespace Inurosen\JsonRPCServer\Interfaces;


interface ValidatorInterface
{
    public function __construct($params);

    public function rules(): array;

    public function validate(): bool;

    public function getErrors(): array;
}
