<?php

namespace AncientEgyptianMuseum\src\Support;

/**
 * String manipulation helper class
 * 
 * Provides utilities for working with strings including pluralization,
 * singularization, and case conversion.
 */
class Str
{
    /**
     * Regular expression patterns for pluralization
     * 
     * @var array<string, string>
     */
    protected static $plural = [
        '/(quiz)$/i' => '$1zes',
        '/^(ox)$/i' => '$1en',
        '/([m|l])ouse$/i' => '$1ice',
        '/(matr|vert|ind)ix|ex$/i' => '$1ices',
        '/(x|ch|ss|sh)$/i' => '$1es',
        '/([^aeiouy]|qu)y$/i' => '$1ies',
        '/(hive)$/i' => '$1s',
        '/(?:([^f])fe|([lr])f)$/i' => '$1$2ves',
        '/(shea|lea|loa|thie)f$/i' => '$1ves',
        '/sis$/i' => 'ses',
        '/([ti])um$/i' => '$1a',
        '/(tomat|potat|ech|her|vet)o$/i' => '$1oes',
        '/(bu)s$/i' => '$1ses',
        '/(alias)$/i' => '$1es',
        '/(octop)us$/i' => '$1i',
        '/(ax|test)is$/i' => '$1es',
        '/(us)$/i' => '$1es',
        '/s$/i' => 's',
        '/$/' => 's'
    ];

    /**
     * Regular expression patterns for singularization
     * 
     * @var array<string, string>
     */
    protected static $singular = [
        '/(quiz)zes$/i' => '$1',
        '/(matr)ices$/i' => '$1ix',
        '/(vert|ind)ices$/i' => '$1ex',
        '/^(ox)en$/i' => '$1',
        '/(alias)es$/i' => '$1',
        '/(octop|vir)i$/i' => '$1us',
        '/(cris|ax|test)es$/i' => '$1is',
        '/(shoe)s$/i' => '$1',
        '/(o)es$/i' => '$1',
        '/(bus)es$/i' => '$1',
        '/([m|l])ice$/i' => '$1ouse',
        '/(x|ch|ss|sh)es$/i' => '$1',
        '/(m)ovies$/i' => '$1ovie',
        '/(s)eries$/i' => '$1eries',
        '/([^aeiouy]|qu)ies$/i' => '$1y',
        '/([lr])ves$/i' => '$1f',
        '/(tive)s$/i' => '$1',
        '/(hive)s$/i' => '$1',
        '/(li|wi|kni)ves$/i' => '$1fe',
        '/(shea|loa|lea|thie)ves$/i' => '$1f',
        '/(^analy)ses$/i' => '$1sis',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '$1$2sis',
        '/([ti])a$/i' => '$1um',
        '/(n)ews$/i' => '$1ews',
        '/(h|bl)ouses$/i' => '$1ouse',
        '/(corpse)s$/i' => '$1',
        '/(us)es$/i' => '$1',
        '/s$/i' => ''
    ];

    /**
     * Irregular word forms that don't follow standard pluralization patterns
     * 
     * @var array<string, string>
     */
    protected static $irregular = [
        'move' => 'moves',
        'foot' => 'feet',
        'goose' => 'geese',
        'sex' => 'sexes',
        'child' => 'children',
        'man' => 'men',
        'tooth' => 'teeth',
        'person' => 'people',
        'valve' => 'valves'
    ];

    /**
     * Words that are the same in both singular and plural forms
     * 
     * @var array<string>
     */
    protected static $uncountable = [
        'sheep',
        'fish',
        'deer',
        'series',
        'species',
        'money',
        'rice',
        'information',
        'equipment'
    ];

    /**
     * Convert a string to its plural form
     *
     * @param string $string The string to pluralize
     * @return string The pluralized string
     */
    public static function plural(string $string): string
    {
        // Check if string is empty
        if (empty($string)) {
            return $string;
        }

        // Save some time in the case that singular and plural are the same
        $lowercase = mb_strtolower($string);
        if (in_array($lowercase, self::$uncountable, true)) {
            return $string;
        }

        // Check for irregular singular forms
        foreach (self::$irregular as $pattern => $result) {
            $pattern = '/^' . preg_quote($pattern, '/') . '$/i';

            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        // Check for matches using regular expressions
        foreach (self::$plural as $pattern => $result) {
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        return $string;
    }

    /**
     * Convert a string to lowercase
     *
     * @param string $string The string to convert
     * @return string The lowercase string
     */
    public static function lower(string $string): string
    {
        return mb_strtolower($string, 'UTF-8');
    }

    /**
     * Convert a string to uppercase
     *
     * @param string $string The string to convert
     * @return string The uppercase string
     */
    public static function upper(string $string): string
    {
        return mb_strtoupper($string, 'UTF-8');
    }

    /**
     * Convert a string to singular form
     *
     * @param string $string The string to singularize
     * @return string The singularized string
     */
    public static function singular(string $string): string
    {
        // Check if string is empty
        if (empty($string)) {
            return $string;
        }

        // Save some time in the case that singular and plural are the same
        $lowercase = mb_strtolower($string);
        if (in_array($lowercase, self::$uncountable, true)) {
            return $string;
        }

        // Check for irregular plural forms
        foreach (self::$irregular as $singular => $plural) {
            $plural = '/^' . preg_quote($plural, '/') . '$/i';

            if (preg_match($plural, $string)) {
                return preg_replace($plural, $singular, $string);
            }
        }

        // Check for matches using regular expressions
        foreach (self::$singular as $pattern => $result) {
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        return $string;
    }

    /**
     * Return a count with properly pluralized word
     *
     * @param int $count The count
     * @param string $string The string to pluralize if needed
     * @return string Formatted string with count and appropriate word form
     */
    public static function plural_if(int $count, string $string): string
    {
        return $count === 1 ? "1 $string" : $count . ' ' . self::plural($string);
    }

    /**
     * Convert a string to title case
     *
     * @param string $string The string to convert
     * @return string The title case string
     */
    public static function title(string $string): string
    {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Convert the first character of a string to uppercase
     *
     * @param string $string The string to convert
     * @return string The string with first character uppercase
     */
    public static function ucfirst(string $string): string
    {
        if (empty($string)) {
            return $string;
        }

        $first = mb_substr($string, 0, 1, 'UTF-8');
        $rest = mb_substr($string, 1, null, 'UTF-8');

        return mb_strtoupper($first, 'UTF-8') . $rest;
    }

    /**
     * Check if a string starts with a given substring
     *
     * @param string $haystack The string to check
     * @param string $needle The substring to find
     * @param bool $caseSensitive Whether the check should be case sensitive
     * @return bool True if the string starts with the given substring
     */
    public static function startsWith(string $haystack, string $needle, bool $caseSensitive = true): bool
    {
        if ($caseSensitive) {
            return strncmp($haystack, $needle, strlen($needle)) === 0;
        }

        return strncasecmp($haystack, $needle, strlen($needle)) === 0;
    }

    /**
     * Check if a string ends with a given substring
     *
     * @param string $haystack The string to check
     * @param string $needle The substring to find
     * @param bool $caseSensitive Whether the check should be case sensitive
     * @return bool True if the string ends with the given substring
     */
    public static function endsWith(string $haystack, string $needle, bool $caseSensitive = true): bool
    {
        $length = strlen($needle);
        if ($length === 0) {
            return true;
        }

        if ($caseSensitive) {
            return substr_compare($haystack, $needle, -$length, $length) === 0;
        }

        return substr_compare(strtolower($haystack), strtolower($needle), -$length, $length) === 0;
    }

    /**
     * Get a substring of the given string
     *
     * @param string $string The string to get substring from
     * @param int $start The starting position
     * @param int|null $length The length of the substring (null for all remaining)
     * @return string The substring
     */
    public static function substr(string $string, int $start, ?int $length = null): string
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * Replace all occurrences of the search string with the replacement string
     *
     * @param string|array $search The value(s) to search for
     * @param string|array $replace The replacement value(s)
     * @param string $subject The string to search and replace in
     * @return string The string with replacements
     */
    public static function replace($search, $replace, string $subject): string
    {
        return str_replace($search, $replace, $subject);
    }

    /**
     * Strip HTML and PHP tags from a string
     *
     * @param string $string The string to strip tags from
     * @param string|null $allowedTags The allowed tags
     * @return string The stripped string
     */
    public static function stripTags(string $string, ?string $allowedTags = null): string
    {
        return strip_tags($string, $allowedTags);
    }
}
