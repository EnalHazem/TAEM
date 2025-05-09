<?php

namespace AncientEgyptianMuseum\Validation\Rules;

use AncientEgyptianMuseum\Validation\Rules\Contract\Rule;

/**
 * ConfirmedRule
 * 
 * Validates that a field matches its confirmation field (e.g., password and password_confirmation)
 */
class ConfirmedRule implements Rule
{
    /** @var string The suffix for the confirmation field */
    protected string $confirmationSuffix;
    
    /** @var string|null The exact confirmation field name, if different from field + suffix */
    protected ?string $confirmationField;
    
    /** @var bool Whether to perform a case-sensitive comparison */
    protected bool $caseSensitive;
    
    /** @var string Custom error message */
    protected string $customMessage = '';
    
    /** @var bool Whether this rule breaks the validation chain when it fails */
    protected bool $breakChain = false;
    
    /**
     * Creates a new confirmed validation rule
     * 
     * @param string $confirmationSuffix The suffix for the confirmation field
     * @param string|null $confirmationField The exact confirmation field name (if different)
     * @param bool $caseSensitive Whether to perform a case-sensitive comparison
     * @param string|null $customMessage Optional custom error message
     * @param bool $breakChain Whether to break the validation chain on failure
     */
    public function __construct(
        string $confirmationSuffix = '_confirmation',
        ?string $confirmationField = null,
        bool $caseSensitive = true,
        ?string $customMessage = null,
        bool $breakChain = false
    ) {
        $this->confirmationSuffix = $confirmationSuffix;
        $this->confirmationField = $confirmationField;
        $this->caseSensitive = $caseSensitive;
        
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
        // Determine the confirmation field name
        $confirmField = $this->confirmationField ?? $field . $this->confirmationSuffix;
        
        // Check if confirmation field exists
        if (!isset($data[$confirmField])) {
            return $this->getMessage($field, $confirmField);
        }
        
        $confirmValue = $data[$confirmField];
        
        // Perform the comparison
        $isValid = $this->caseSensitive
            ? $value === $confirmValue
            : strcasecmp((string) $value, (string) $confirmValue) === 0;
        
        return $isValid ? true : $this->getMessage($field, $confirmField);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMessage(string $field, string $confirmField = null): string
    {
        if (!empty($this->customMessage)) {
            return str_replace(
                ['%s', '%c'], 
                [$field, $confirmField ?? $field . $this->confirmationSuffix], 
                $this->customMessage
            );
        }
        
        $message = '%s does not match %c';
        return str_replace(
            ['%s', '%c'], 
            [$field, $confirmField ?? $field . $this->confirmationSuffix],
            $message
        );
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
        return '%s does not match %s confirmation';
    }
}