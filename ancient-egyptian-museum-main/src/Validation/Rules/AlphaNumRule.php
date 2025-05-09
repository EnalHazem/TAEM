<?php

namespace AncientEgyptianMuseum\Validation\Rules;

use AncientEgyptianMuseum\Validation\Rules\Contract\Rule;

/**
 * AlphaNumRule
 * 
 * Validates that a field contains only alphanumeric characters, spaces, underscores and hyphens
 */
class AlphaNumRule implements Rule
{
    /** @var bool Whether to allow spaces */
    protected bool $allowSpaces;
    
    /** @var bool Whether to allow underscores */
    protected bool $allowUnderscores;
    
    /** @var bool Whether to allow hyphens */
    protected bool $allowHyphens;
    
    /** @var string Custom error message */
    protected string $customMessage = '';
    
    /** @var bool Whether this rule breaks the validation chain when it fails */
    protected bool $breakChain = false;
    
    /**
     * Creates a new alphanumeric validation rule
     * 
     * @param bool $allowSpaces Whether to allow spaces
     * @param bool $allowUnderscores Whether to allow underscores
     * @param bool $allowHyphens Whether to allow hyphens
     * @param string|null $customMessage Optional custom error message
     * @param bool $breakChain Whether to break the validation chain on failure
     */
    public function __construct(
        bool $allowSpaces = true,
        bool $allowUnderscores = true,
        bool $allowHyphens = true,
        ?string $customMessage = null,
        bool $breakChain = false
    ) {
        $this->allowSpaces = $allowSpaces;
        $this->allowUnderscores = $allowUnderscores;
        $this->allowHyphens = $allowHyphens;
        
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
        // Skip validation if value is empty
        if ($value === null || $value === '') {
            return true;
        }
        
        // Build regex pattern based on configuration
        $pattern = '/^[a-zA-Z0-9';
        if ($this->allowSpaces) {
            $pattern .= ' ';
        }
        if ($this->allowUnderscores) {
            $pattern .= '_';
        }
        if ($this->allowHyphens) {
            $pattern .= '-';
        }
        $pattern .= ']+$/';
        
        $isValid = preg_match($pattern, (string) $value) === 1;
        
        return $isValid ? true : $this->getMessage($field);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMessage(string $field): string
    {
        if (!empty($this->customMessage)) {
            return str_replace('%s', $field, $this->customMessage);
        }
        
        // Build a more descriptive message based on configuration
        $allowedChars = 'alphanumeric characters';
        $extras = [];
        
        if ($this->allowSpaces) {
            $extras[] = 'spaces';
        }
        if ($this->allowUnderscores) {
            $extras[] = 'underscores';
        }
        if ($this->allowHyphens) {
            $extras[] = 'hyphens';
        }
        
        if (!empty($extras)) {
            $allowedChars .= ' and ' . implode(', ', $extras);
        }
        
        return str_replace('%s', $field, "%s must contain only $allowedChars");
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
        return '%s must be alpha numeric only';
    }
}