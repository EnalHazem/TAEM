<?php

namespace AncientEgyptianMuseum\Validation\Rules;

use AncientEgyptianMuseum\Validation\Rules\Contract\Rule;

/**
 * EmailRule
 * 
 * Validates that a field contains a valid email address
 */
class EmailRule implements Rule
{
    /** @var bool Whether to check DNS records for the email domain */
    protected bool $checkDns;
    
    /** @var bool Whether to use the PHP filter_var function instead of regex */
    protected bool $useFilter;
    
    /** @var string Custom error message */
    protected string $customMessage = '';
    
    /** @var bool Whether this rule breaks the validation chain when it fails */
    protected bool $breakChain = false;
    
    /**
     * Creates a new email validation rule
     * 
     * @param bool $checkDns Whether to check DNS records for the email domain
     * @param bool $useFilter Whether to use PHP's filter_var instead of regex
     * @param string|null $customMessage Optional custom error message
     * @param bool $breakChain Whether to break the validation chain on failure
     */
    public function __construct(
        bool $checkDns = false,
        bool $useFilter = true,
        ?string $customMessage = null,
        bool $breakChain = false
    ) {
        $this->checkDns = $checkDns;
        $this->useFilter = $useFilter;
        
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
        
        $value = (string) $value;
        $isValid = false;
        
        if ($this->useFilter) {
            // Use PHP's built-in email validation
            $isValid = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        } else {
            // Use custom regex pattern which matches the RFC standard better
            $isValid = preg_match(
                "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/",
                $value
            ) === 1;
        }
        
        if ($isValid && $this->checkDns) {
            // Check that the domain has valid MX records
            $domain = substr($value, strpos($value, '@') + 1);
            $isValid = checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
        }
        
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
        return 'Your %s is not a valid email address';
    }
}