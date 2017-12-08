<?php
namespace BrainDiminished\Evaluable\Compiler\Context;

/**
 * Generic operator descriptor
 */
abstract class OperatorDescriptor
{
    /** @var OperatorArity */
    public $arity;

    public function __construct(OperatorArity $arity)
    {
        $this->arity = $arity;
    }

    abstract public function precede(OperatorDescriptor $o2): bool;
}
