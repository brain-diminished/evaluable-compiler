<?php
namespace BrainDiminished\Compiler\Atom;

final class PrefixSymbol extends OperatorAtom {
    public function largc()
    {
        return $this->meta->arity()->argc();
    }
}
