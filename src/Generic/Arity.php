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

    /** @var ?string */
    private $separator;

    /** @var ?string */
    private $delimiter;

    /** @var int */
    private $leftPriority;

    /** @var int */
    private $rightPriority;

    /** @var bool */
    private $isPrefix;


    public function __construct(?int $argc = 2, bool $isPrefix = false, int $rightPriority = 0, int $leftPriority = 0, ?string $separator = null, ?string $delimiter = null)
    {
        if ($argc < 0 || $argc === 0) {
            throw new EnvironmentException('Invalid arity: operand count must be strictly positive');
        }
        $this->argc = $argc;

        if (($argc === null || ($isPrefix ? $argc : $argc - 1) > 1) && empty($separator)) {
            throw new EnvironmentException('Invalid arity: a non-empty separator is mandatory in case of multiple operand count');
        }
        $this->separator = $separator;

        if ($argc === null && empty($delimiter)) {
            throw new EnvironmentException('Invalid arity: a non-empty delimiter is mandatory in case of variable operand count');
        }
        $this->delimiter = $delimiter;

        $this->rightPriority = $rightPriority;
        $this->leftPriority = $leftPriority;
        $this->isPrefix = $isPrefix;
    }


    final public function argc(): ?int
    {
        return $this->argc;
    }

    final public function separator(): ?string
    {
        return $this->separator;
    }

    final public function delimiter(): ?string
    {
        return $this->delimiter;
    }

    final public function leftPriority(): int
    {
        return $this->leftPriority;
    }

    final public function rightPriority(): int
    {
        return $this->rightPriority;
    }

    final public function isPrefix(): bool
    {
        return $this->isPrefix;
    }
}
