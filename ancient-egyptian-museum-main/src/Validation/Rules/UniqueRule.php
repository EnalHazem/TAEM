<?php

namespace AncientEgyptianMuseum\Validation\Rules;

use AncientEgyptianMuseum\Validation\Rules\Contract\Rule;

/**
 * UniqueRule
 * 
 * Validates that a field value is unique in the database table
 */
class UniqueRule implements Rule
{
    /** @var string The database table to check against */
    protected string $table;
    
    /** @var string The database column to check against */
    protected string $column;
    
    /** @var string|null The name of the ID column for ignore condition */
    protected ?string $idColumn;
    
    /** @var mixed|null The ID value to ignore */
    protected mixed $ignoreId;
    
    /** @var array Additional where conditions */
    protected array $whereConditions;
    
    /** @var string Custom error message */
    protected string $customMessage = '';
    
    /** @var bool Whether this rule breaks the validation chain when it fails */
    protected bool $breakChain = false;
    
    /**
     * Creates a new unique validation rule
     * 
     * @param string $table The database table
     * @param string $column The database column
     * @param string|null $idColumn The ID column name for the ignore condition
     * @param mixed|null $ignoreId The ID value to ignore
     * @param array $whereConditions Additional where conditions as ['column' => 'value']
     * @param string|null $customMessage Optional custom error message
     * @param bool $breakChain Whether to break the validation chain on failure
     */
    public function __construct(
        string $table,
        string $column,
        ?string $idColumn = null,
        mixed $ignoreId = null,
        array $whereConditions = [],
        ?string $customMessage = null,
        bool $breakChain = false
    ) {
        $this->table = $table;
        $this->column = $column;
        $this->idColumn = $idColumn;
        $this->ignoreId = $ignoreId;
        $this->whereConditions = $whereConditions;
        
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
        
        try {
            // Start building the query
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$this->column} = ?";
            $params = [$value];
            
            // Add ignore condition if provided
            if ($this->idColumn !== null && $this->ignoreId !== null) {
                $query .= " AND {$this->idColumn} != ?";
                $params[] = $this->ignoreId;
            }
            
            // Add additional where conditions
            foreach ($this->whereConditions as $column => $conditionValue) {
                $query .= " AND {$column} = ?";
                $params[] = $conditionValue;
            }
            
            // Execute the query
            $result = app()->db->raw($query, $params);
            
            // Check if any records were found
            $isUnique = is_array($result) ? (int)($result[0]['count'] ?? 0) === 0 : true;
            
            return $isUnique ? true : $this->getMessage($field);
        } catch (\Exception $e) {
            // Log the error silently and return validation error
            error_log("UniqueRule database error: " . $e->getMessage());
            return $this->getMessage($field);
        }
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
        return 'This %s is already taken';
    }
    
    /**
     * Add an additional where condition to the unique check
     * 
     * @param string $column The column name
     * @param mixed $value The value to compare against
     * @return self
     */
    public function where(string $column, mixed $value): self
    {
        $this->whereConditions[$column] = $value;
        return $this;
    }
    
    /**
     * Add an ignore condition to the unique check
     * 
     * @param mixed $id The ID value to ignore
     * @param string $idColumn The ID column name
     * @return self
     */
    public function ignore(mixed $id, string $idColumn = 'id'): self
    {
        $this->idColumn = $idColumn;
        $this->ignoreId = $id;
        return $this;
    }
}