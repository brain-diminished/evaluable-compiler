<?php
namespace BrainDiminished\Evaluable\Compiler\Context;

use BrainDiminished\Evaluable\Evaluable;

/**
 * A prefix operator occurs before an operand.
 * Arity cannot be zero, since there are no applicable argument on the left.
 */
abstract class PrefixOperatorDescriptor extends OperatorDescriptor
{
    final public function precede(OperatorDescriptor $o2): bool
    {
        return true;
    }

    /**
     * @param Evaluable[] $args
     * @return Evaluable
     */
    abstract public function instantiate(array $args): Evaluable;
}
