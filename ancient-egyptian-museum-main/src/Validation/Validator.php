<?php

namespace AncientEgyptianMuseum\Validation;

use AncientEgyptianMuseum\Validation\Rules\Contract\Rule;
use InvalidArgumentException;

/**
 * Main Validator class responsible for validating data against a set of rules
 */
class Validator
{
    /**
     * Data to be validated
     */
    protected array $data = [];

    /**
     * Human-readable field names (aliases)
     */
    protected array $aliases = [];

    /**
     * Validation rules mapped to fields
     */
    protected array $rules = [];

    /**
     * Custom error messages
     */
    protected array $customMessages = [];

    /**
     * Error bag containing validation errors
     */
    protected ErrorBag $errorBag;

    /**
     * Indicates if validation should stop on first failure
     */
    protected bool $stopOnFirstFailure = false;

    /**
     * Fields that should be validated
     */
    protected ?array $onlyFields = null;

    /**
     * Fields that should be excluded from validation
     */
    protected array $exceptFields = [];

    /**
     * Create a new validator instance
     */
    public function __construct(array $data = [], array $rules = [], array $aliases = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->aliases = $aliases;
        $this->errorBag = new ErrorBag();
    }

    /**
     * Set up data for validation and run validation
     */
    public function make($data)
    {
        $this->data = $data;
        $this->errorBag = new ErrorBag();
        $this->validate();

        return $this;
    }

    /**
     * Validate data against rules
     */
    protected function validate()
    {
        foreach ($this->rules as $field => $rules) {
            // Skip field if it's in the except list
            if (in_array($field, $this->exceptFields)) {
                continue;
            }

            // Skip field if onlyFields is set and field is not in the list
            if ($this->onlyFields !== null && !in_array($field, $this->onlyFields)) {
                continue;
            }

            $ruleObjects = RulesResolver::make($rules);
            foreach ($ruleObjects as $rule) {
                $this->applyRule($field, $rule);

                // Stop validation if requested and there are errors
                if ($this->stopOnFirstFailure && !empty($this->errors($field))) {
                    break;
                }
            }

            // Stop validation if requested and there are errors
            if ($this->stopOnFirstFailure && !empty($this->errors())) {
                break;
            }
        }
    }

    /**
     * Apply a rule to a field
     */
    protected function applyRule($field, Rule $rule)
    {
        if (!$rule->apply($field, $this->getFieldValue($field), $this->data)) {
            $errorMessage = $this->getErrorMessage($field, $rule);
            $this->errorBag->add($field, $errorMessage);
        }
    }

    /**
     * Get a custom error message if defined, otherwise use default
     */
    protected function getErrorMessage($field, Rule $rule)
    {
        $ruleClass = get_class($rule);
        $ruleName = lcfirst(basename(str_replace('\\', '/', $ruleClass)));
        $ruleName = str_replace('Rule', '', $ruleName);

        // Check for custom message
        if (isset($this->customMessages["$field.$ruleName"])) {
            return $this->customMessages["$field.$ruleName"];
        }

        if (isset($this->customMessages[$ruleName])) {
            return $this->customMessages[$ruleName];
        }

        // Fall back to default message
        return Message::generate($rule, $this->alias($field));
    }

    /**
     * Get a field's value from the data
     */
    protected function getFieldValue($field)
    {
        // Handle dot notation for nested arrays
        if (str_contains($field, '.')) {
            return $this->getNestedValue($field);
        }

        return $this->data[$field] ?? null;
    }

    /**
     * Get a nested value using dot notation
     */
    protected function getNestedValue($key)
    {
        $keys = explode('.', $key);
        $value = $this->data;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Set validation rules
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Add rules for a specific field
     */
    public function addRules($field, $rules)
    {
        $this->rules[$field] = $rules;
        return $this;
    }

    /**
     * Set field aliases for error messages
     */
    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;
        return $this;
    }

    /**
     * Add a single alias
     */
    public function addAlias($field, $alias)
    {
        $this->aliases[$field] = $alias;
        return $this;
    }

    /**
     * Get field alias
     */
    public function alias($field)
    {
        return $this->aliases[$field] ?? $field;
    }

    /**
     * Set custom error messages
     */
    public function setCustomMessages(array $messages)
    {
        $this->customMessages = $messages;
        return $this;
    }

    /**
     * Set whether validation should stop on first failure
     */
    public function stopOnFirstFailure($stop = true)
    {
        $this->stopOnFirstFailure = $stop;
        return $this;
    }

    /**
     * Only validate specific fields
     */
    public function only(array $fields)
    {
        $this->onlyFields = $fields;
        return $this;
    }

    /**
     * Exclude specific fields from validation
     */
    public function except(array $fields)
    {
        $this->exceptFields = $fields;
        return $this;
    }

    /**
     * Check if validation passes
     */
    public function passes()
    {
        return empty($this->errors());
    }

    /**
     * Check if validation fails
     */
    public function fails()
    {
        return !$this->passes();
    }

    /**
     * Get validation errors
     */
    public function errors($key = null)
    {
        return $key ? ($this->errorBag->errors[$key] ?? []) : $this->errorBag->errors;
    }

    /**
     * Get the first error message for a field
     */
    public function getFirstError($field)
    {
        $errors = $this->errors($field);
        return !empty($errors) ? $errors[0] : null;
    }

    /**
     * Check if a field has errors
     */
    public function hasError($field)
    {
        return !empty($this->errors($field));
    }

    /**
     * Add an error message manually
     */
    public function addError($field, $message)
    {
        $this->errorBag->add($field, $message);
        return $this;
    }

    /**
     * Get validated data (only fields that passed validation)
     */
    public function getValidData()
    {
        $validData = [];

        foreach ($this->rules as $field => $rule) {
            if (empty($this->errors($field)) && array_key_exists($field, $this->data)) {
                if (str_contains($field, '.')) {
                    // For nested fields, just get the top level
                    $topField = explode('.', $field)[0];
                    $validData[$topField] = $this->data[$topField];
                } else {
                    $validData[$field] = $this->data[$field];
                }
            }
        }

        return $validData;
    }

    /**
     * Create a validator instance and check if validation passes
     */
    public static function validateData($data, $rules, $aliases = [])
    {
        $validator = new static($data, $rules, $aliases);
        $validator->validate();
        return $validator->passes();
    }
}
