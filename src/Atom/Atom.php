<?php
namespace BrainDiminished\Compiler\Atom;

abstract class Atom
{
    const PREFIX_OPERATOR   = 1 << 0;
    const INFIX_OPERATOR    = 1 << 1;
    const DELIMITER         = 1 << 2;

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
