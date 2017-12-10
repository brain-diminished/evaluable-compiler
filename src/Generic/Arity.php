<?php
namespace BrainDiminished\Compiler\Generic;

use BrainDiminished\Compiler\Exception\EnvironmentException;

/**
 * This class declares the information needed by the compiler in order to process correctly an operator arguments.
 */
class Arity
{
    /** @var ?int */
    private $argc;

    /** @var int */
    private $leftPriority;

    /** @var int */
    private $rightPriority;


    public function __construct(?int $argc = 2, int $rightPriority = 0, int $leftPriority = 0)
    {
        $this->argc = $argc;

        if (($argc === null || $argc > 1) && empty($separator)) {
            throw new EnvironmentException('Invalid arity: a non-empty separator is mandatory in case of multiple operand count');
        }
        $this->separator = $separator;

        if ($argc === null && empty($delimiter)) {
            throw new EnvironmentException('Invalid arity: a non-empty delimiter is mandatory in case of variable operand count');
        }
        $this->delimiter = $delimiter;

        $this->rightPriority = $rightPriority;
        $this->leftPriority = $leftPriority;
    }


    final public function argc(): ?int
    {
        return $this->argc;
    }

    final public function leftPriority(): int
    {
        return $this->leftPriority;
    }

    final public function rightPriority(): int
    {
        return $this->rightPriority;
    }
}
