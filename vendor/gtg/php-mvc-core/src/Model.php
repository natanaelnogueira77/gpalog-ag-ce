<?php 

namespace GTG\MVC;

use DateTime;

abstract class Model 
{
    public const RULE_RAW = 'raw';
    public const RULE_DATETIME = 'date';
    public const RULE_EMAIL = 'email';
    public const RULE_IN = 'in';
    public const RULE_INT = 'int';
    public const RULE_MATCH = 'match';
    public const RULE_MAX = 'max';
    public const RULE_MIN = 'min';
    public const RULE_EQUAL_TO = 'equal_to';
    public const RULE_SMALLER_THAN = 'smaller_than';
    public const RULE_LARGER_THAN = 'larger_than';
    public const RULE_REQUIRED = 'required';

    public array $errors = [];
    protected $values = [];

    public function __get($key) 
    {
        return $this->values[$key] ?? null;
    }

    public function __set($key, $value) 
    {
        $this->values[$key] = $value;
    }

    public function loadData(array $data): static 
    {
        foreach($data as $key => $value) {
            if(property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    abstract public function rules(): array;

    public function validate(): bool
    {
        foreach($this->rules() as $attribute => $rules) {
            if($attribute === self::RULE_RAW) {
                foreach($rules as $function) {
                    if(is_callable($function)) {
                        $function($this);
                    }
                }
            } else {
                $value = $this->{$attribute};
                foreach($rules as $rule) {
                    $ruleName = $rule;
                    if(!is_string($ruleName)) {
                        $ruleName = $rule[0];
                    }
    
                    if($ruleName === self::RULE_REQUIRED && !$value) {
                        $this->addErrorForRule($attribute, self::RULE_REQUIRED, $rule);
                    }
    
                    if($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addErrorForRule($attribute, self::RULE_EMAIL, $rule);
                    }
    
                    if($ruleName === self::RULE_INT && !filter_var($value, FILTER_VALIDATE_INT)) {
                        $this->addErrorForRule($attribute, self::RULE_INT, $rule);
                    }
                    
                    if($ruleName === self::RULE_MIN && $value && strlen($value) < $rule['min']) {
                        $this->addErrorForRule($attribute, self::RULE_MIN, $rule);
                    }
    
                    if($ruleName === self::RULE_MAX && $value && strlen($value) > $rule['max']) {
                        $this->addErrorForRule($attribute, self::RULE_MAX, $rule);
                    }
    
                    if($ruleName === self::RULE_MATCH && $value !== $this->{$rule['match']}) {
                        $this->addErrorForRule($attribute, self::RULE_MATCH, $rule);
                    }
                    
                    if($ruleName === self::RULE_EQUAL_TO && $value != $rule['value']) {
                        $this->addErrorForRule($attribute, self::RULE_EQUAL_TO, $rule);
                    }

                    if($ruleName === self::RULE_SMALLER_THAN && $value >= $rule['value']) {
                        $this->addErrorForRule($attribute, self::RULE_SMALLER_THAN, $rule);
                    }

                    if($ruleName === self::RULE_LARGER_THAN && $value <= $rule['value']) {
                        $this->addErrorForRule($attribute, self::RULE_LARGER_THAN, $rule);
                    }
    
                    if($ruleName === self::RULE_IN && $value && !in_array($value, $rule['values'])) {
                        $this->addErrorForRule($attribute, self::RULE_IN, $rule);
                    }
    
                    if($ruleName === self::RULE_DATETIME && $value && !DateTime::createFromFormat($rule['pattern'], $value)) {
                        $this->addErrorForRule($attribute, self::RULE_DATETIME, $rule);
                    }
                }
            }
        }

        return empty($this->errors);
    }

    private function addErrorForRule(string $attribute, string $rule, array $params = []): void
    {
        $message = $params['message'] ?? '';
        foreach($params as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        $this->errors[$attribute][] = $message;
    }

    public function addError(string $attribute, string $message): void
    {
        $this->errors[$attribute][] = $message;
    }

    public function hasError(string $attribute): array|false
    {
        return $this->errors[$attribute] ?? false;
    }

    public function hasErrors(): bool 
    {
        return $this->errors ? true : false;
    }

    public function getFirstError(string $attribute): string|false
    {
        return $this->errors[$attribute][0] ?? false;
    }

    public function getFirstErrors(): array
    {
        $errors = [];
        foreach($this->errors as $attr => $messages) {
            $errors[$attr] = $messages[0];
        }
        return $errors;
    }
}