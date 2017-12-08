<?php
namespace BrainDiminished\Evaluable\Compiler\Token;

use BrainDiminished\Evaluable\Compiler\Context\OperatorDescriptor;

abstract class OperatorToken extends Token
{
    /** @var OperatorDescriptor */
    public $descriptor;

    public function __construct(string $symbol, int $position, OperatorDescriptor $descriptor)
    {
        parent::__construct($symbol, $position);
        $this->descriptor = $descriptor;
    }
}
