<?php
namespace BrainDiminished\Compiler\Atom;

use BrainDiminished\Compiler\Generic\MetaOperator;

abstract class OperatorAtom extends Atom
{
    /** @var MetaOperator */
    private $meta;

    public function __construct(string $symbol, int $position, MetaOperator $meta)
    {
        parent::__construct($symbol, $position);
        $this->meta = $meta;
    }

    final public function meta(): MetaOperator
    {
        return $this->meta;
    }

    final public function argc(): ?int
    {
        $rargc = $this->rargc();
        return $rargc === null ? null : $rargc + $this->largc();
    }

    final public function rargc(): ?int
    {
        return $this->meta->arity()->argc();
    }

    abstract public function largc(): int;
}
