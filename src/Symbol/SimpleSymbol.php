<?php
namespace BrainDiminished\Compiler\Symbol;

class SimpleSymbol implements Symbol
{
    private $pattern;

    public function __construct(string $expression, bool $isRegex = false)
    {
        $this->pattern = $isRegex ? $expression : preg_quote($expression);
    }

    public function pattern(): string
    {
        return $this->pattern;
    }
}
