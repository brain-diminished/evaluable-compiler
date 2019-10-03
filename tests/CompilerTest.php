<?php
namespace BrainDiminished\Test\Compiler;

use BrainDiminished\Compiler\Compiler;
use BrainDiminished\Compiler\Environment\CompilationEnvironment;
use BrainDiminished\Compiler\Generic\Arity;
use BrainDiminished\Compiler\Generic\MetaOperator;
use BrainDiminished\Evaluable\Evaluable;
use BrainDiminished\Evaluable\RuntimeContext;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    public function test()
    {
        $environment = new EnvironmentTest;
        $compiler = new Compiler($environment);
        $expression = '-34 + -7 - 3*2';
        $evaluable = $compiler->compile($expression);
        $result = $evaluable->evaluate();
        $this->assertEquals(-34 + -7 - 3*2, $result);

        $result = $compiler->compile('3²')->evaluate();
        $this->assertEquals(9, $result);
    }
}

class EnvironmentTest implements CompilationEnvironment
{
    private $prefixOps;
    private $infixOps;
    private $prefixPattern;
    private $infixPattern;

    public function __construct()
    {
        $this->constants = [

        ];

        $this->prefixOps = [
            '-' => new UnaryOperator('-'),
            '+' => new UnaryOperator('+'),
            '!' => new UnaryOperator('!'),
            '~' => new UnaryOperator('~'),
        ];
        $this->infixOps = [
            '+' => new BinaryOperator('+', 5),
            '-' => new BinaryOperator('-', 5),
            '*' => new BinaryOperator('*', 4),
            '/' => new BinaryOperator('/', 4),
            '||' => new BinaryOperator('||', 14),
            '&&' => new BinaryOperator('&&', 12),
            '²' => new StaticPowOperator(2),
        ];

        $this->prefixPattern = '\d+(\.\d*)?|(\.\d*)?\d+|true\b|false\b|null\b|'.implode('|', array_map('preg_quote', array_keys($this->prefixOps)));
        $this->infixPattern = implode('|', array_map('preg_quote', array_keys($this->infixOps)));
    }

    private function opPattern($op)
    {
        $quoted = preg_quote($op);
        return "(?<$op>$quoted)";
    }

    public function getBlankPattern(): string { return '[ \n\r\t]+'; }
    public function getAtomPattern(): string { return '\d+(\.\d*)?|(\.\d*)?\d+|true\b|false\b|null\b'; }
    public function buildAtom(KeywordAtom $symbol): Evaluable
    {
        switch(true) {
            case $symbol->symbol == 'true': return new Constant(true);
            case $symbol->symbol == 'false': return new Constant(false);
            case $symbol->symbol == 'null': return new Constant(null);
            case preg_match('@^\d+(\.\d*)?|(\.\d*)?\d+&@', $symbol->symbol): return new Constant(floatval($symbol->symbol));
            default: throw new \Exception;
        }
    }
    public function getPrefixOperatorPattern(): string { return $this->prefixPattern; }
    public function getPrefixOperator(string $symbol, ?string $pregId): MetaOperator { return $this->prefixOps[$symbol]; }
    public function getInfixOperatorPattern(): string { return implode('|', array_map('preg_quote', array_keys($this->infixOps))); }
    public function getInfixOperator(string $symbol, ?string $pregId): MetaOperator { return $this->infixOps[$symbol]; }
}

class Constant implements Evaluable
{
    private $value;
    public function __construct($value) {$this->value = $value; }
    public function evaluate(RuntimeContext $context = null) { return $this->value; }
}

class MetaBinaryOperator extends MetaOperator
{
    /** @var string */
    private $op;

    public function __construct(string $op, int $priority = 5, $isLeftAssociative = true)
    {
        parent::__construct(new Arity(2, false, $priority, $priority - $isLeftAssociative ? 1 : 0));
        $this->op = $op;
    }

    public function evaluate(array $args, RuntimeContext $context = null)
    {
        $lhs = $args[0]->evaluate($context);
        $rhs = $args[1]->evaluate($context);
        switch ($this->op) {
            case '+': return $lhs + $rhs;
            case '-': return $lhs - $rhs;
            case '*': return $lhs * $rhs;
            case '/': return $lhs / $rhs;
            case '||': return $lhs || $rhs;
            case '&&': return $lhs && $rhs;
            default: throw new \Exception;
        }
    }

    /**
     * @param Evaluable[] $args
     * @return Evaluable
     */
    public function build(array $args = []): Evaluable
    {
        // TODO: Implement build() method.
    }
}

class MetaUnaryOperator extends MetaOperator
{
    /** @var string */
    private $op;

    public function __construct(string $op, int $priority = 3)
    {
        parent::__construct(new Arity(1, true, $priority));
        $this->op = $op;
    }

    public function evaluate(array $args, RuntimeContext $context = null)
    {
        switch ($this->op) {
            case '-': return - $args[0]->evaluate($context);
            case '+': return + $args[0]->evaluate($context);
            case '!': return ! $args[0]->evaluate($context);
            case '~': return ~ $args[0]->evaluate($context);
        }
    }

    /**
     * @param Evaluable[] $args
     * @return Evaluable
     */
    public function build(array $args = []): Evaluable
    {
        // TODO: Implement build() method.
    }
}

class MetaStaticPowOperator extends MetaOperator
{
    private $pow;

    public function __construct(int $pow)
    {
        parent::__construct(new Arity(1, false));
        $this->pow = $pow;
    }

    public function evaluate(array $args, RuntimeContext $context = null)
    {
        return $args[0]->evaluate($context) ** $this->pow;
    }
}

class Operator implements Evaluable
{

}