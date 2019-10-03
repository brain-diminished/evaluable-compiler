<?php
namespace BrainDiminished\Compiler\Environment;


use BrainDiminished\Compiler\Generic\MetaOperator;
use BrainDiminished\Compiler\Symbol\SymbolPool;

class Environment
{
    /** @var SymbolPool */
    private $prefixOperators;

    /** @var SymbolPool */
    private $infixOperators;

    /**
     * Environment constructor.
     * @param MetaOperator[] $operators
     */
    public function __construct(array $operators)
    {
        $this->infixOperators = new SymbolPool();
        $this->prefixOperators = new SymbolPool();
        foreach ($operators as $operator) {
            $this->addOperator($operator);
        }
    }

    public function addOperator(MetaOperator $operator)
    {
        if ($operator->infix()) {
            $this->infixOperators->add($operator);
        } else {
            $this->prefixOperators->add($operator);
        }
    }

    public function infixOperators(): SymbolPool
    {
        return $this->infixOperators;
    }

    public function prefixOperators(): SymbolPool
    {
        return $this->prefixOperators;
    }
}