<?php


namespace Inurosen\JsonRPCServer;


use Inurosen\JsonRPCServer\Interfaces\RuleInterface;
use Inurosen\JsonRPCServer\Interfaces\ValidatorInterface;

class Validator implements ValidatorInterface
{
    protected $params;
    protected $errors = [];
    private $rules = [];

    public function __construct($params, $rules = [])
    {
        $this->params = $params;
        $this->rules = $rules;
    }

    public function validate(): bool
    {
        if ($this->isRulesAssoc()) {
            foreach ($this->rules() as $fieldName => $fieldRules) {
                $this->applyFieldRule($fieldName, $fieldRules);
            }
        } else {
            $this->applyFieldRule(0, $this->rules());
        }

        return count($this->errors) === 0;
    }

    private function isRulesAssoc()
    {
        if ([] === $this->rules()) {
            return false;
        }

        return array_keys($this->rules()) !== range(0, count($this->rules()) - 1);
    }

    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * @param $fieldName
     * @param $fieldRules
     */
    private function applyFieldRule($fieldName, $fieldRules): void
    {
        array_walk($fieldRules, function ($fieldRule, $idx, $fieldName) {
            $toValidate = $this->params;
            if ($fieldName === 0) {
                $toValidate = [0 => $this->params];
            }
            if (is_callable($fieldRule) && !$fieldRule instanceof RuleInterface) {
                call_user_func_array($fieldRule, [$fieldName, $toValidate]);
            } else {
                if (!method_exists($fieldRule, 'handle')) {
                    throw new \Exception(JsonRPCService::E_MSG_VALIDATOR_NOT_FOUND,
                        JsonRPCService::E_CODE_VALIDATOR_NOT_FOUND);
                }
                /** @var RuleInterface $rule */
                if (is_object($fieldRule) && $fieldRule instanceof RuleInterface) {
                    $rule = $fieldRule;
                } else {
                    $rule = new $fieldRule;
                }

                if (!call_user_func_array([$rule, 'handle'], [$fieldName, $toValidate])) {
                    $this->addError($fieldName, $rule->getMessage());
                }
            }
        }, $fieldName);
    }

    final protected function addError($field, $error)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $error;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
