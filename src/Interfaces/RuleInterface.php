<?php


namespace Inurosen\JsonRPCServer\Interfaces;


interface RuleInterface
{
    public static function make($rule, $message, $options = []): self;

    public function handle($field, $params): bool;

    public function getMessage(): string;
}
