<?php

namespace AncientEgyptianMuseum\Validation\Rules\Contract;

interface Rule extends \Stringable
{
    
    public function apply(string $field, mixed $value, array $data = []): bool|string;
    
  
    public function getMessage(string $field): string;
    
    
    public function shouldBreakChain(): bool;
}