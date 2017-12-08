<?php
namespace BrainDiminished\Evaluable\Compiler\Context;

use BrainDiminished\Evaluable\Evaluable;

/**
 * An infix operator occurs after a valid operand (may be an atom or the result of another operator).
 * Note that if arity is zero, it is actually a postfix operator.
 */
abstract class InfixOperatorDescriptor extends OperatorDescriptor
{
    final public function precede(OperatorDescriptor $o2): bool
    {
        if ($o2->arity->isClosed()
            || ($o2->arity->isFixed($argc) && $argc == 0)) {
            return false;
        }
        $p1 = $this->arity->getLeftPriority();
        $p2 = $o2->arity->getRightPriority();
        return $p1 < $p2;
    }

    /**
     * @param Evaluable $lhs
     * @param Evaluable[] $rhs
     * @return Evaluable
     */
    abstract public function instantiate(Evaluable $lhs, array $rhs): Evaluable;
}
