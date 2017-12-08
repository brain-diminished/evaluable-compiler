<?php
namespace BrainDiminished\Evaluable\Compiler\Context;

use BrainDiminished\Evaluable\Evaluable;

/**
 * A CompilationContext is what drives the compiler, the three patterns (atom, prefix op and infix op), along with the
 * arity of the operators, define the reading rule.
 */
interface CompilationContext
{
    public function getSafetyChar(): string;

    public function getAtomPattern(): string;
    public function buildAtom(string $symbol): Evaluable;

    public function getPrefixOperatorPattern(): string;
    public function getPrefixOperatorDescriptor(string $symbol): PrefixOperatorDescriptor;

    public function getInfixOperatorPattern(): string;
    public function getInfixOperatorDescriptor(string $symbol): InfixOperatorDescriptor;
}
