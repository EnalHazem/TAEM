<?php

namespace AncientEgyptianMuseum\Validation\Rules;

use AncientEgyptianMuseum\Validation\Rules\Contract\Rule;

/**
 * BetweenRule
 * 
 * Validates that a field's length or value is between a minimum and maximum value
 */
class BetweenRule implements Rule
{
    /** @var int|float The minimum allowed value */
    protected int|float $min;
    
    /** @var int|float The maximum allowed value */
    protected int|float $max;
    
    /** @var string The validation mode: 'length', 'value', 'file', or 'array' */
    protected string $mode;
    
    /** @var string Custom error message */
    protected string $customMessage = '';
    
    /** @var bool Whether this rule breaks the validation chain when it fails */
    protected bool $breakChain = false;
    
    /**
     * Creates a new between validation rule
     * 
     * @param int|float $min The minimum value
     * @param int|float $max The maximum value
     * @param string $mode The validation mode (length, value, file, array)
     * @param string|null $customMessage Optional custom error message
     * @param bool $breakChain Whether to break the validation chain on failure
     * 
     * @throws \InvalidArgumentException If min is greater than max
     */
    public function __construct(
        int|float $min, 
        int|float $max, 
        string $mode = 'length',
        ?string $customMessage = null,
        bool $breakChain = false
    ) {
        if ($min > $max) {
            throw new \InvalidArgumentException('The minimum value cannot be greater than the maximum value');
        }
        
        $this->min = $min;
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
        
        $isValid = false;
        
        switch ($this->mode) {
            case 'length':
                $length = strlen((string) $value);
                $isValid = $length >= $this->min && $length <= $this->max;
                break;
                
            case 'value':
                if (!is_numeric($value)) {
                    return $this->getMessage($field);
                }
                $numericValue = (float) $value;
                $isValid = $numericValue >= $this->min && $numericValue <= $this->max;
                break;
                
            case 'file':
                // For file uploads (in KB)
                if (!isset($value['size'])) {
                    return $this->getMessage($field);
                }
                $sizeInKB = $value['size'] / 1024;
                $isValid = $sizeInKB >= $this->min && $sizeInKB <= $this->max;
                break;
                
            case 'array':
                // For array counts
                if (!is_array($value)) {
                    return $this->getMessage($field);
                }
                $count = count($value);
                $isValid = $count >= $this->min && $count <= $this->max;
                break;
                
            default:
                $length = strlen((string) $value);
                $isValid = $length >= $this->min && $length <= $this->max;
        }
        
        return $isValid ? true : $this->getMessage($field);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMessage(string $field): string
    {
        if (!empty($this->customMessage)) {
            return str_replace(['%s', '%min', '%max'], [$field, $this->min, $this->max], $this->customMessage);
        }
        
        $message = match($this->mode) {
            'length' => "%s must be between {$this->min} and {$this->max} characters",
            'value' => "%s must be between {$this->min} and {$this->max}",
            'file' => "%s file size must be between {$this->min} and {$this->max} KB",
            'array' => "%s must contain between {$this->min} and {$this->max} items",
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
        return "%s must be between {$this->min} and {$this->max} characters";
    }
}