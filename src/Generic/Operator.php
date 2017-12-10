<?php
namespace BrainDiminished\Compiler\Generic;

use BrainDiminished\Evaluable\Evaluable;
use BrainDiminished\Evaluable\RuntimeContext;

class Operator implements Evaluable
{
    /** @var MetaOperator */
    private $meta;

    /** @var Evaluable[] */
    private $args;

    public function __construct(MetaOperator $meta, array $args)
    {
        $this->meta = $meta;
        $this->args = $args;
    }

    public function evaluate(RuntimeContext $context = null)
    {
        return $this->meta->evaluate($this->args, $context);
    }
}