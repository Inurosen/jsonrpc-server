<?php
/**
 *  This file is part of JSON-RPC 2.0 Server Library
 *
 * (c) Renat Khaertdinov <inurosen@inurosen.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TestValidatorSimpleArray extends \Inurosen\JsonRPCServer\Validator
{
    public function rules(): array
    {
        return [
            \Inurosen\JsonRPCServer\Rules\RequiredRule::class,
            \Inurosen\JsonRPCServer\Rules\ArrayRule::class,
        ];
    }
}
