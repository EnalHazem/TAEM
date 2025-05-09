<?php

namespace AncientEgyptianMuseum\Validation;

use InvalidArgumentException;

/**
 * Trait RulesResolver
 * 
 * Provides methods to parse and resolve validation rules from different formats
 * into their corresponding rule objects.
 */
trait RulesResolver
{
    /**
     * Creates rule objects from various input formats
     * 
     * Supports:
     * - String format: 'required|email|max:255'
     * - Array format: ['required', 'email', 'max:255']
     * - Mixed format: ['required', 'between:5,10', SomeRuleObject]
     * 
     * @param string|array $rules The rules to parse and resolve
     * @return array Array of instantiated rule objects
     * @throws InvalidArgumentException When rule format is invalid
     */
    public static function make($rules): array
    {
        // Handle empty rules
        if (empty($rules)) {
            return [];
        }

        // Convert single rule string to array
        if (is_string($rules)) {
            $rules = self::parseRulesString($rules);
        }

        // Ensure rules is an array
        if (!is_array($rules)) {
            throw new InvalidArgumentException("Rules must be a string or an array");
        }

        // Parse and instantiate each rule
        $resolvedRules = [];
        foreach ($rules as $rule) {
            if (is_string($rule)) {
                $resolvedRules[] = static::getRuleFromString($rule);
            } elseif (is_object($rule)) {
                $resolvedRules[] = $rule; // Rule is already an object
            } else {
                throw new InvalidArgumentException("Invalid rule format: " . gettype($rule));
            }
        }

        return $resolvedRules;
    }

    /**
     * Parses a string of rules into an array
     * 
     * @param string $rulesString Rules in format "rule1|rule2:param1,param2|rule3"
     * @return array Parsed rules array
     */
    protected static function parseRulesString(string $rulesString): array
    {
        if (empty($rulesString)) {
            return [];
        }
        
        // Handle both pipe-separated and single rule formats
        return str_contains($rulesString, '|') ? explode('|', $rulesString) : [$rulesString];
    }

    /**
     * Creates a rule object from a rule string
     * 
     * @param string $rule Rule in format "name:param1,param2,..."
     * @return object The instantiated rule object
     * @throws InvalidArgumentException When rule format is invalid or rule doesn't exist
     */
    public static function getRuleFromString(string $rule): object
    {
        $rule = trim($rule);
        if (empty($rule)) {
            throw new InvalidArgumentException("Rule string cannot be empty");
        }

        // Extract rule name and parameters
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        
        // Parse parameters if they exist
        $parameters = [];
        if (isset($parts[1])) {
            $parameters = self::parseParameters($parts[1]);
        }
        
        // Resolve and instantiate the rule
        try {
            return RulesMapper::resolve($ruleName, $parameters);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Failed to resolve rule '$rule': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Parses parameters from a parameter string
     * 
     * Handles:
     * - Basic comma-separated values
     * - Quoted strings that may contain commas
     * - Type casting for numeric and boolean values
     * 
     * @param string $paramString Parameter string in format "param1,param2,..."
     * @return array Parsed parameters
     */
    protected static function parseParameters(string $paramString): array
    {
        if (empty($paramString)) {
            return [];
        }
        
        $parameters = [];
        $chunks = explode(',', $paramString);
        
        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            
            // Skip empty parameters
            if ($chunk === '') {
                continue;
            }
            
            // Type casting for numeric values
            if (is_numeric($chunk)) {
                $parameters[] = strpos($chunk, '.') !== false ? (float)$chunk : (int)$chunk;
                continue;
            }
            
            // Handle boolean values
            if ($chunk === 'true') {
                $parameters[] = true;
                continue;
            }
            
            if ($chunk === 'false') {
                $parameters[] = false;
                continue;
            }
            
            // Handle string values
            $parameters[] = $chunk;
        }
        
        return $parameters;
    }

    /**
     * Batch resolves multiple rule strings at once
     * 
     * @param array $rulesStrings Array of rule strings
     * @return array Associative array of rule strings to their resolved objects
     */
    public static function batchResolve(array $rulesStrings): array
    {
        $result = [];
        
        foreach ($rulesStrings as $ruleString) {
            $result[$ruleString] = static::getRuleFromString($ruleString);
        }
        
        return $result;
    }

    /**
     * Converts rule objects back to their string representation
     * 
     * @param array $ruleObjects Array of rule objects
     * @return string Rules in string format "rule1|rule2:param1,param2|rule3"
     */
    public static function toString(array $ruleObjects): string
    {
        $rules = [];
        
        foreach ($ruleObjects as $rule) {
            if (method_exists($rule, 'toString')) {
                $rules[] = $rule->toString();
            } elseif (method_exists($rule, '__toString')) {
                $rules[] = (string)$rule;
            } else {
                // Try to get class short name as fallback
                $className = (new \ReflectionClass($rule))->getShortName();
                $ruleName = strtolower(str_replace('Rule', '', $className));
                $rules[] = $ruleName;
            }
        }
        
        return implode('|', $rules);
    }
}