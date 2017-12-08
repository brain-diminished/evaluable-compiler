<?php
namespace BrainDiminished\Evaluable\Compiler\Token;

abstract class Token
{
    const ATOM              = 1 << 0;
    const PREFIX_OPERATOR   = 1 << 1;
    const INFIX_OPERATOR    = 1 << 2;
    const DELIMITER         = 1 << 3;

    /** @var string */
    public $symbol;

    /** @var int */
    public $position;

    public function __construct(string $symbol, int $position)
    {
        $this->symbol = $symbol;
        $this->position = $position;
    }
}
