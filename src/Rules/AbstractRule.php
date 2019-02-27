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


use Inurosen\JsonRPCServer\Interfaces\RuleInterface;
use Inurosen\JsonRPCServer\JsonRPCService;

abstract class AbstractRule implements RuleInterface
{
    protected $message = '';
    protected $options = [];

    public static function make($rule = null, $message = null, $options = []): RuleInterface
    {
        $rule = $rule ?? static::class;
        if (!class_exists($rule)) {
            throw new \Exception(JsonRPCService::E_MSG_VALIDATOR_NOT_FOUND,
                JsonRPCService::E_CODE_VALIDATOR_NOT_FOUND);
        }

        /**
         * @var AbstractRule $ruleObject
         */
        $ruleObject = new $rule;
        !$message ?: $ruleObject->setMessage($message);
        $ruleObject->setOptions($options);

        return $ruleObject;
    }

    protected function setOptions($options)
    {
        $this->options = $options;
    }

    public function handle($field, $params): bool
    {
        return true;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    protected function setMessage($message)
    {
        $this->message = $message;
    }
}
