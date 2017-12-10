<?php
namespace BrainDiminished\Compiler\Atom;

use BrainDiminished\Compiler\Generic\MetaOperator;

abstract class OperatorAtom extends Atom
{
    /** @var MetaOperator */
    public $meta;

    public function __construct(string $symbol, int $position, MetaOperator $meta)
    {
        parent::__construct($symbol, $position);
        $this->meta = $meta;
    }

    abstract public function largc();
}
