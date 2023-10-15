<?php

namespace AHAbid\Laravalerors;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use ReflectionClass;

class LaravalerorsException extends ValidationException
{
    /**
     * Provide empty params for rules
     */
    protected array $emptyParamsForRules = ['unique'];

    /**
     * Callback on each error iteration
     */
    protected $ruleErrorCallback;

    /**
     * Set rules to provide empty params
     */
    public function setEmptyParamsForRules(array $rules = []): static
    {
        $this->emptyParamsForRules = $rules;

        return $this;
    }

    /**
     * Set callback for rule error iteration
     */
    public function setRuleErrorCallback(?callable $ruleErrorCallback = null): static
    {
        $this->ruleErrorCallback = $ruleErrorCallback;

        return $this;
    }

    /**
     * Get all of the validation error messages for API.
     *
     * @return array
     */
    public function errorsForApi(): array
    {
        $result = [];
        $errors = $this->validator->errors()->messages();

        foreach ($this->validator->failed() as $field => $rules) {
            if (!isset($errors[$field])) {
                continue;
            }

            $result[$field] = [];

            $i = 0;
            foreach ($rules as $rule => $params) {
                $ruleError = $this->extractRuleNameAndOptions($rule, $params);
                $ruleError['message'] = $errors[$field][$i] ?? '';

                if (is_callable($this->ruleErrorCallback)) {
                    $ruleError = call_user_func($this->ruleErrorCallback);
                }

                $result[$field][] = $ruleError;
                $i++;
            }
        }

        return $result;
    }

    /**
     * Extract rule name & parameters
     */
    protected function extractRuleNameAndOptions(string $ruleClass, array $params): array
    {
        $ruleName = mb_strtolower($ruleClass);
        $ruleParams = $params;

        if (class_exists($ruleClass)) {
            [$ruleName, $ruleParams] = $this->getCustomRuleNameAndOptionsFromRuleClass($ruleClass);
        }

        return [
            'rule' => $ruleName,
            'options' => $this->filerRuleParams($ruleName, $ruleParams),
        ];
    }

    /**
     * Get custom rule name & params if available from rule class
     *
     * @return [string, array]
     */
    protected function getCustomRuleNameAndOptionsFromRuleClass(string $ruleClass): array
    {
        $refection = new ReflectionClass($ruleClass);
        $props = $refection->getConstants() ?? [];

        $ruleName = array_key_exists('RULE_NAME', $props)
            ? mb_strtolower($props['RULE_NAME'])
            : Str::of($refection->getShortName())->snake('-')->lower();

        $ruleParams = $props['RULE_PARAMS'] ?? [];

        return [$ruleName, $ruleParams];
    }

    /**
     * Filter Rule Params
     */
    protected function filerRuleParams(string $ruleName, array $params): array
    {
        if (in_array($ruleName, $this->emptyParamsForRules)) {
            return [];
        }

        return $params;
    }
}
