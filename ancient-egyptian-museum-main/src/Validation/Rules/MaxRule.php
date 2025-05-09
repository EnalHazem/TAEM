<?php

namespace AncientEgyptianMuseum\Validation\Rules;

use AncientEgyptianMuseum\Validation\Rules\Contract\Rule;

/**
 * MaxRule
 * 
 * Validates that a field's length or value does not exceed a maximum value
 */
class MaxRule implements Rule
{
    /** @var int The maximum allowed value */
    protected int $max;
    
    /** @var string The validation mode: 'length', 'value', 'file', or 'array' */
    protected string $mode;
    
    /** @var string Custom error message */
    protected string $customMessage = '';
    
    /** @var bool Whether this rule breaks the validation chain when it fails */
    protected bool $breakChain = false;
    
    /**
     * Creates a new maximum validation rule
     * 
     * @param int $max The maximum value
     * @param string $mode The validation mode (length, value, file, array)
     * @param string|null $customMessage Optional custom error message
     * @param bool $breakChain Whether to break the validation chain on failure
     */
    public function __construct(
        int $max, 
        string $mode = 'length', 
        ?string $customMessage = null, 
        bool $breakChain = false
    ) {
        $this->max = $max;
        $this->mode = $mode;
        
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
        // Skip validation if value is empty (let RequiredRule handle this)
        if ($value === null || $value === '') {
            return true;
        }
        
        switch ($this->mode) {
            case 'length':
                $valid = strlen((string) $value) <= $this->max;
                break;
                
            case 'value':
                $valid = is_numeric($value) && (float) $value <= $this->max;
                break;
                
            case 'file':
                // For file uploads (in bytes)
                $valid = isset($value['size']) && $value['size'] <= ($this->max * 1024); // KB to bytes
                break;
                
            case 'array':
                // For array counts
                $valid = is_array($value) && count($value) <= $this->max;
                break;
                
            default:
                $valid = strlen((string) $value) <= $this->max;
        }
        
        return $valid ? true : $this->getMessage($field);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMessage(string $field): string
    {
        if (!empty($this->customMessage)) {
            return str_replace('%s', $field, $this->customMessage);
        }
        
        $message = match($this->mode) {
            'length' => "%s must be less than or equal to {$this->max} characters",
            'value' => "%s must be less than or equal to {$this->max}",
            'file' => "%s file size must be less than or equal to {$this->max} KB",
            'array' => "%s must contain at most {$this->max} items",
            default => $this->__toString()
        };
        
        return str_replace('%s', $field, $message);
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
        return "%s must be less than or equal to {$this->max} characters";
    }
}