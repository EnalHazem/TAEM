<?php

namespace AncientEgyptianMuseum\Validation;

/**
 * Message
 * 
 * Handles validation error message generation and formatting
 */
class Message
{
    /**
     * Default error messages for validation rules
     * 
     * @var array<string, string>
     */
    protected static array $defaultMessages = [
        'required' => '%s is required and cannot be empty',
        'email' => '%s must be a valid email address',
        'min' => '%s must be at least %d characters',
        'max' => '%s must be less than or equal to %d characters',
        'between' => '%s must be between %d and %d characters',
        'alpha' => '%s must contain only alphabetic characters',
        'alphaNum' => '%s must contain only alphanumeric characters',
        'numeric' => '%s must be a number',
        'integer' => '%s must be an integer',
        'float' => '%s must be a floating point number',
        'url' => '%s must be a valid URL',
        'confirmed' => '%s confirmation does not match',
        'unique' => '%s has already been taken',
        'date' => '%s must be a valid date',
        'in' => '%s must be one of: %s',
        'notIn' => '%s must not be one of: %s',
        'regex' => '%s format is invalid',
        'boolean' => '%s must be true or false',
        'accepted' => '%s must be accepted',
        'ip' => '%s must be a valid IP address',
        'file' => '%s must be a file',
        'uploaded' => '%s must be uploaded',
        'image' => '%s must be an image',
        'dimensions' => '%s has invalid image dimensions',
        'mimes' => '%s must be a file of type: %s',
        'exists' => 'The selected %s is invalid',
    ];
    
    /**
     * Custom error messages for validation rules
     * 
     * @var array<string, string>
     */
    protected static array $customMessages = [];
    
    /**
     * Add custom error messages
     * 
     * @param array<string, string> $messages Custom messages
     * @return void
     */
    public static function setCustomMessages(array $messages): void
    {
        static::$customMessages = array_merge(static::$customMessages, $messages);
    }
    
    /**
     * Get a custom error message for a rule
     * 
     * @param string $rule The rule name
     * @param string|null $field The field name (for field-specific messages)
     * @return string|null The custom message or null if not found
     */
    public static function getCustomMessage(string $rule, ?string $field = null): ?string
    {
        // Check for field-specific message first
        if ($field !== null && isset(static::$customMessages["{$field}.{$rule}"])) {
            return static::$customMessages["{$field}.{$rule}"];
        }
        
        // Then check for general rule message
        return static::$customMessages[$rule] ?? null;
    }
    
    /**
     * Generate a validation error message
     * 
     * @param string|object $rule The rule name or object
     * @param string $field The field name
     * @param array<mixed> $params Additional parameters for message placeholders
     * @return string The generated error message
     */
    public static function generate(string|object $rule, string $field, array $params = []): string
    {
        $ruleName = is_object($rule) ? (new \ReflectionClass($rule))->getShortName() : $rule;
        $ruleName = strtolower(str_replace('Rule', '', $ruleName));
        
        // Get message template from custom messages or default messages or use the rule itself as template
        $template = static::getCustomMessage($ruleName, $field) 
            ?? static::$defaultMessages[$ruleName] 
            ?? (is_string($rule) ? $rule : (string) $rule);
        
        // Replace field placeholder
        $message = str_replace('%s', $field, $template);
        
        // Replace additional parameters
        if (!empty($params)) {
            $placeholders = array_map(fn($i) => "%{$i}", range(1, count($params)));
            $message = str_replace($placeholders, $params, $message);
        }
        
        return $message;
    }
    
    /**
     * Format a validation message
     * 
     * @param string $message The raw message
     * @param array<string, mixed> $replacements Key-value pairs of replacements
     * @return string The formatted message
     */
    public static function format(string $message, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $placeholder = ":{$key}";
            $message = str_replace($placeholder, (string) $value, $message);
        }
        
        return $message;
    }
    
    /**
     * Translate a validation message
     * 
     * @param string $message The message to translate
     * @param string|null $locale The locale to use, or null for default
     * @return string The translated message
     */
    public static function translate(string $message, ?string $locale = null): string
    {
        // This is a placeholder for a translation system
        // Implement your translation logic here
        
        return $message;
    }
    
    /**
     * Get all default messages
     * 
     * @return array<string, string>
     */
    public static function getDefaultMessages(): array
    {
        return static::$defaultMessages;
    }
    
    /**
     * Get all custom messages
     * 
     * @return array<string, string>
     */
    public static function getCustomMessages(): array
    {
        return static::$customMessages;
    }
    
    /**
     * Reset custom messages
     * 
     * @return void
     */
    public static function resetCustomMessages(): void
    {
        static::$customMessages = [];
    }
}