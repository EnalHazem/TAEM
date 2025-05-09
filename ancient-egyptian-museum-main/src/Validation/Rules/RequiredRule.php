<?php

namespace AncientEgyptianMuseum\Validation\Rules;

use AncientEgyptianMuseum\Validation\Rules\Contract\Rule;

/**
 * RequiredRule
 * 
 * Validates that a field is not empty
 */
class RequiredRule implements Rule
{
    /** @var string Custom error message */
    protected string $customMessage = '';
    
    /** @var bool Whether this rule breaks the validation chain when it fails */
    protected bool $breakChain = true;
    
    /**
     * Creates a new required rule
     * 
     * @param string|null $customMessage Optional custom error message
     * @param bool $breakChain Whether to break the validation chain on failure
     */
    public function __construct(?string $customMessage = null, bool $breakChain = true)
    {
        if ($customMessage !== null) {
            $this->customMessage = $customMessage;
        }
        $this->breakChain = $breakChain;
    }
    
    /**
     * {@inheritdoc}
     */
    public function apply(string $field, mixed $value, array $data = []): bool|string
    {
        // Proper empty check for various data types
        if ($value === null || $value === '' || $value === [] || $value === false) {
            return $this->getMessage($field);
        }
        
        // Special check for strings with only whitespace
        if (is_string($value) && trim($value) === '') {
            return $this->getMessage($field);
        }
        
        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMessage(string $field): string
    {
        if (!empty($this->customMessage)) {
            return str_replace('%s', $field, $this->customMessage);
        }
        
        return str_replace('%s', $field, $this->__toString());
    }
    
    /**
     * {@inheritdoc}
     */
    public function shouldBreakChain(): bool
    {
        return $this->breakChain;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return '%s is required and cannot be empty';
    }
}