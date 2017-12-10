<?php
namespace BrainDiminished\Compiler\Generic;

use BrainDiminished\Compiler\Exception\EnvironmentException;
use BrainDiminished\Compiler\Symbol\Symbol;
use BrainDiminished\Evaluable\Evaluable;

abstract class MetaOperator implements Symbol
{
    private $signature;

    /** @var Symbol */
    private $symbol;

    /** @var Arity */
    private $arity;

    /** @var bool */
    private $infix;

    /** @var Symbol|null */
    private $separator;

    /** @var Symbol|null */
    private $delimiter;

    public function __construct(Symbol $symbol, Arity $arity, bool $infix = true, ?Symbol $separator = null, ?Symbol $delimiter = null)
    {
        $this->symbol = $symbol;
        $this->arity = $arity;
        $this->separator = $separator;
        $this->delimiter = $delimiter;

        if (($arity->argc() === null || $arity->argc() > ($infix ? 2 : 1)) && $separator === null) {
            throw new EnvironmentException('Invalid operator: a separator symbol is mandatory in case of multiple right arity');
        }

        if (($arity->argc() === null) && $delimiter === null) {
            throw new EnvironmentException('Invalid operator: a delimiter symbol is mandatory in case of variable arity');
        }
    }

    final public function infix(): bool
    {
        return $this->infix;
    }

    final public function arity(): Arity
    {
        return $this->arity;
    }

    final public function pattern(): string
    {
        return $this->symbol->pattern();
    }

    final public function delimiter(): ?Symbol
    {
        return $this->delimiter;
    }

    final public function separator(): ?Symbol
    {
        return $this->separator;
    }

    /**
     * @param Evaluable[] $args
     * @return Evaluable
     */
    abstract public function build(array $args = []): Evaluable;
}
