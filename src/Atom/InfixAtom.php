<?php
namespace BrainDiminished\Compiler\Atom;

use BrainDiminished\Compiler\Generic\MetaOperator;

final class InfixAtom extends OperatorAtom
{
    public function __construct($symbol, $position, MetaOperator $meta)
    {
        parent::__construct($symbol, $position, $meta);
    }

    public function largc()
    {
        $argc = $this->meta->arity()->argc();
        return $argc === null ? $argc : $argc - 1;
    }
}
