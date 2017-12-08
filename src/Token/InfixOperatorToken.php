<?php
namespace BrainDiminished\Evaluable\Compiler\Token;

use BrainDiminished\Evaluable\Compiler\Context\InfixOperatorDescriptor;

/**
 * @property InfixOperatorDescriptor $descriptor
 */
final class InfixOperatorToken extends OperatorToken
{
    public function __construct(string $symbol, int $position, InfixOperatorDescriptor $descriptor)
    {
        parent::__construct($symbol, $position, $descriptor);
    }
}
