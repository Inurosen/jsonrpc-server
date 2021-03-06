<?php
/**
 *  This file is part of JSON-RPC 2.0 Server Library
 *
 * (c) Renat Khaertdinov <inurosen@inurosen.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inurosen\JsonRPCServer\Rules;


class IntegerRule extends AbstractRule
{
    protected $message = 'Field must be integer';

    public function handle($field, $params): bool
    {
        return !isset($params[$field]) || is_int($params[$field]);
    }
}
