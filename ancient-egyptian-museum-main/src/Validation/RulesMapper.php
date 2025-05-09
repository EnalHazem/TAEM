<?php

namespace AncientEgyptianMuseum\Validation;

use AncientEgyptianMuseum\Validation\Rules\MaxRule;
use AncientEgyptianMuseum\Validation\Rules\EmailRule;
use AncientEgyptianMuseum\Validation\Rules\UniqueRule;
use AncientEgyptianMuseum\Validation\Rules\BetweenRule;
use AncientEgyptianMuseum\Validation\Rules\AlphaNumRule;
use AncientEgyptianMuseum\Validation\Rules\RequiredRule;
use AncientEgyptianMuseum\Validation\Rules\ConfirmedRule;
use InvalidArgumentException;

/**
 * Trait RulesMapper
 * 
 * Maps validation rule names to their corresponding rule class implementations,
 * allowing dynamic rule resolution and instantiation.
 */
trait RulesMapper
{
    /**
     * Map of rule names to their corresponding class implementations
     * 
     * @var array<string, string>
     */
    protected static array $map = [
        'required' => RequiredRule::class,
        'alnum' => AlphaNumRule::class,
        'max' => MaxRule::class,
        'between' => BetweenRule::class,
        'email' => EmailRule::class,
        'confirmed' => ConfirmedRule::class,
        'unique' => UniqueRule::class,
    ];

    /**
     * Resolves a rule name to its corresponding rule object
     * 
     * @param string $rule The rule name to resolve
     * @param array $options The options to pass to the rule constructor
     * @return object The instantiated rule object
     * @throws InvalidArgumentException When the rule is not registered
     */
    public static function resolve(string $rule, array $options = []): object
    {
        if (!isset(static::$map[$rule])) {
            throw new InvalidArgumentException("Validation rule '$rule' is not registered");
        }

        $ruleClass = static::$map[$rule];
        return new $ruleClass(...$options);
    }

    /**
     * Registers a new validation rule
     * 
     * @param string $name The rule name
     * @param string $class The fully qualified class name that implements the rule
     * @return void
     * @throws InvalidArgumentException When the class doesn't exist
     */
    public static function register(string $name, string $class): void
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Rule class '$class' does not exist");
        }
        
        static::$map[$name] = $class;
    }

    /**
     * Checks if a rule is registered
     * 
     * @param string $rule The rule name to check
     * @return bool True if the rule is registered, false otherwise
     */
    public static function has(string $rule): bool
    {
        return isset(static::$map[$rule]);
    }

    /**
     * Get all registered validation rules
     * 
     * @return array<string, string> The registered rules map
     */
    public static function getRules(): array
    {
        return static::$map;
    }
}