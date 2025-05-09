<?php

namespace AncientEgyptianMuseum\Validation;

/**
 * ErrorBag
 * 
 * Container for validation errors organized by field
 */
class ErrorBag implements \ArrayAccess, \Countable, \JsonSerializable, \IteratorAggregate
{
    /**
     * The array of validation error messages
     * 
     * @var array<string, array<int, string>>
     */
    protected array $errors = [];
    
    /**
     * Add an error message for a specific field
     * 
     * @param string $field The field name
     * @param string $message The error message
     * @return self
     */
    public function add(string $field, string $message): self
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
        
        return $this;
    }
    
    /**
     * Add multiple error messages for a specific field
     * 
     * @param string $field The field name
     * @param array<string> $messages The error messages
     * @return self
     */
    public function addMany(string $field, array $messages): self
    {
        foreach ($messages as $message) {
            $this->add($field, $message);
        }
        
        return $this;
    }
    
    /**
     * Merge another error bag into this one
     * 
     * @param ErrorBag $errorBag The error bag to merge
     * @return self
     */
    public function merge(ErrorBag $errorBag): self
    {
        foreach ($errorBag->all() as $field => $messages) {
            foreach ($messages as $message) {
                $this->add($field, $message);
            }
        }
        
        return $this;
    }
    
    /**
     * Get all errors
     * 
     * @return array<string, array<int, string>>
     */
    public function all(): array
    {
        return $this->errors;
    }
    
    /**
     * Get the first error for a specific field
     * 
     * @param string|null $field The field name, or null for first error of any field
     * @return string|null The first error message or null if none exist
     */
    public function first(?string $field = null): ?string
    {
        if ($field === null) {
            foreach ($this->errors as $messages) {
                if (!empty($messages)) {
                    return $messages[0];
                }
            }
            return null;
        }
        
        return $this->errors[$field][0] ?? null;
    }
    
    /**
     * Get all errors for a specific field
     * 
     * @param string $field The field name
     * @return array<int, string> Array of error messages
     */
    public function get(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Check if there are any errors
     * 
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    /**
     * Check if a field has errors
     * 
     * @param string $field The field name
     * @return bool
     */
    public function has(string $field): bool
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }
    
    /**
     * Clear all errors or errors for a specific field
     * 
     * @param string|null $field The field name to clear, or null for all fields
     * @return self
     */
    public function clear(?string $field = null): self
    {
        if ($field === null) {
            $this->errors = [];
        } else if (isset($this->errors[$field])) {
            unset($this->errors[$field]);
        }
        
        return $this;
    }
    
    /**
     * Get errors as a flattened array
     * 
     * @return array<int, string> Flattened array of all error messages
     */
    public function flatten(): array
    {
        $flattenedErrors = [];
        
        foreach ($this->errors as $messages) {
            foreach ($messages as $message) {
                $flattenedErrors[] = $message;
            }
        }
        
        return $flattenedErrors;
    }
    
    /**
     * Format errors for display
     * 
     * @param string $format Format string with placeholders (:field, :message)
     * @return array<int, string> Formatted error messages
     */
    public function format(string $format = ':field: :message'): array
    {
        $formatted = [];
        
        foreach ($this->errors as $field => $messages) {
            foreach ($messages as $message) {
                $formatted[] = str_replace([':field', ':message'], [$field, $message], $format);
            }
        }
        
        return $formatted;
    }
    
    /**
     * Get HTML formatted errors
     * 
     * @param string $wrapperTag The HTML tag to wrap all errors (e.g., 'div', 'ul')
     * @param string $itemTag The HTML tag to wrap each error (e.g., 'span', 'li')
     * @param array<string, string> $attributes HTML attributes for the wrapper tag
     * @return string HTML formatted errors
     */
    public function toHtml(
        string $wrapperTag = 'div', 
        string $itemTag = 'span',
        array $attributes = ['class' => 'validation-errors']
    ): string {
        if (!$this->hasErrors()) {
            return '';
        }
        
        $attributesStr = '';
        foreach ($attributes as $key => $value) {
            $attributesStr .= " {$key}=\"{$value}\"";
        }
        
        $html = "<{$wrapperTag}{$attributesStr}>";
        
        foreach ($this->errors as $field => $messages) {
            foreach ($messages as $message) {
                $html .= "<{$itemTag} data-field=\"{$field}\">{$message}</{$itemTag}>";
            }
        }
        
        $html .= "</{$wrapperTag}>";
        
        return $html;
    }
    
    /**
     * Get error messages as JSON
     * 
     * @return string JSON encoded error messages
     */
    public function toJson(): string
    {
        return json_encode($this->errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Magic method to access errors as properties
     * 
     * @param string $name The field name
     * @return array<int, string>|null Array of error messages or null
     */
    public function __get(string $name): ?array
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        
        return $this->get($name);
    }
    
    /**
     * Check if a field has errors (magic method)
     * 
     * @param string $name The field name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }
    
    /**
     * Convert errors to string
     * 
     * @return string String representation of all errors
     */
    public function __toString(): string
    {
        return implode(PHP_EOL, $this->flatten());
    }
    
    /**
     * Get the number of error fields (for Countable interface)
     * 
     * @return int The number of fields with errors
     */
    public function count(): int
    {
        return count($this->errors);
    }
    
    /**
     * Check if a field exists (for ArrayAccess interface)
     * 
     * @param mixed $offset The field name
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string) $offset);
    }
    
    /**
     * Get errors for a field (for ArrayAccess interface)
     * 
     * @param mixed $offset The field name
     * @return array<int, string>
     */
    public function offsetGet(mixed $offset): array
    {
        return $this->get((string) $offset);
    }
    
    /**
     * Set errors for a field (for ArrayAccess interface)
     * 
     * @param mixed $offset The field name
     * @param mixed $value The error message(s)
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $field = (string) $offset;
        $this->errors[$field] = is_array($value) ? $value : [$value];
    }
    
    /**
     * Remove errors for a field (for ArrayAccess interface)
     * 
     * @param mixed $offset The field name
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->clear((string) $offset);
    }
    
    /**
     * Get iterator for errors (for IteratorAggregate interface)
     * 
     * @return \ArrayIterator<string, array<int, string>>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->errors);
    }
    
    /**
     * Prepare errors for JSON serialization (for JsonSerializable interface)
     * 
     * @return array<string, array<int, string>>
     */
    public function jsonSerialize(): array
    {
        return $this->errors;
    }
}