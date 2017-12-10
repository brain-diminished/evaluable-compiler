<?php
namespace BrainDiminished\Compiler\Generic;

use BrainDiminished\Evaluable\Evaluable;
use BrainDiminished\Evaluable\RuntimeContext;

abstract class MetaOperator
{
    /** @var Arity */
    private $arity;

    public function __construct(Arity $arity)
    {
        $this->arity = $arity;
    }

    final public function arity(): Arity
    {
        return $this->arity;
    }

    final public function build(array $args): Evaluable
    {
        return new Operator($this, $args);
    }

    abstract public function evaluate(array $args, RuntimeContext $context = null);
}
