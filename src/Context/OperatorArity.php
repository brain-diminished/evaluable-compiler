<?php
namespace BrainDiminished\Evaluable\Compiler\Context;

/**
 * This interface declares the information needed by the compiler in ortder to process correctly an operator arguments.
 */
interface OperatorArity
{
    public function isFixed(int &$argc = null): bool;
    public function isPlural(string &$separator = null): bool;
    public function isClosed(string &$delimiter = null): bool;
    public function allowNoArg(): bool;
    public function getLeftPriority(): int;
    public function getRightPriority(): int;
}
