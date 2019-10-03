<?php
namespace BrainDiminished\Compiler\Stream;

use BrainDiminished\Compiler\Symbol\Symbol;
use BrainDiminished\Compiler\Symbol\SymbolPool;

class SymbolStream
{
    /** @var string */
    private $expression;

    /** @var string */
    private $stream;

    /** @var Symbol */
    private $current;

    public function current(): ?Symbol
    {
        return $this->current;
    }

    public function __construct(string $expression)
    {
        $this->expression = $expression;
        $this->stream = $expression;
    }

    public function skip(string $pattern)
    {
        if (preg_match("(^($pattern))", $this->stream, $matches)) {
            $this->stream = substr($this->stream, strlen($matches[0]));
        }
    }

    public function tryReadPool(SymbolPool $pool, Symbol &$symbol = null, string &$match = null): bool
    {
        if ($pool->preg($this->stream, $symbol, $match)) {
            $this->stream = substr($this->stream, strlen($match));
            return true;
        } else {
            return false;
        }
    }

    public function tryReadSymbol(Symbol $symbol, string &$match = null): bool
    {
        $pattern = $symbol->pattern();
        if (preg_match("(^($pattern))", $this->stream, $matches)) {
            $match = $matches[0];
            $this->stream = substr($this->stream, strlen($match));
            return true;
        } else {
            return false;
        }
    }
}
