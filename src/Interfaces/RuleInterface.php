<?php
/**
 *  This file is part of JSON-RPC 2.0 Server Library
 *
 * (c) Renat Khaertdinov <inurosen@inurosen.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inurosen\JsonRPCServer\Interfaces;


interface RuleInterface
{
    public static function make($rule, $message, $options = []): self;

    public function handle($field, $params): bool;

    public function getMessage(): string;
}
