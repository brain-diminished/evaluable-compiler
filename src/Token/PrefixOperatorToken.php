<?php
namespace BrainDiminished\Evaluable\Compiler\Token;

use BrainDiminished\Evaluable\Compiler\Context\PrefixOperatorDescriptor;

final class PrefixOperatorToken extends OperatorToken
{
    public function __construct(string $symbol, int $position, PrefixOperatorDescriptor $descriptor)
    {
        parent::__construct($symbol, $position, $descriptor);
    }
}
