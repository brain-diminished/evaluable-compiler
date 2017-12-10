<?php
namespace BrainDiminished\Compiler\Environment;

use BrainDiminished\Compiler\Generic\MetaOperator;
use BrainDiminished\Evaluable\Evaluable;

/**
 * An environment to drive the compiler:
 * - the patterns help reading the different symbols from the raw source.
 * - the arity of the operators help reading the arguments.
 * - whenever the compiler has read enough symbols to build an evaluable instruction, the environment will handle
 * the construction of these.
 *
 * Note that the patterns should NOT be delimited.
 */
interface CompilationEnvironment
{
    /**
     * A pattern to match what should be ignored (may be spaces, new lines, comments, etc.).
     * @return string
     */
    public function getBlankPattern(): string;

    /**
     * A pattern to match a prefix operator.
     * @return string
     */
    public function getPrefixOperatorPattern(): string;

    /**
     * Retrieve a prefix meta operator identified by its symbol.
     * @param string $symbol
     * @return MetaOperator
     */
    public function getPrefixOperator(string $symbol, ?string $pregId): MetaOperator;

    /**
     * A pattern to match an infix operator (ie with one argument on the left side).
     * @return string
     */
    public function getInfixOperatorPattern(): string;

    /**
     * Retrieve an infix meta operator identified by its symbol.
     * @param string $symbol
     * @return MetaOperator
     */
    public function getInfixOperator(string $symbol, ?string $pregId): MetaOperator;
}
