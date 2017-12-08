<?php
namespace BrainDiminished\Evaluable\Compiler;

use BrainDiminished\Evaluable\Compiler\Stream\CompilationStream;
use BrainDiminished\Evaluable\Compiler\Token\AtomToken;
use BrainDiminished\Evaluable\Compiler\Token\DelimiterToken;
use BrainDiminished\Evaluable\Compiler\Token\InfixOperatorToken;
use BrainDiminished\Evaluable\Compiler\Token\OperatorToken;
use BrainDiminished\Evaluable\Compiler\Token\PrefixOperatorToken;
use BrainDiminished\Evaluable\Compiler\Token\Token;
use BrainDiminished\Evaluable\Compiler\Context\CompilationContext;
use BrainDiminished\Evaluable\Compiler\Context\InfixOperatorDescriptor;
use BrainDiminished\Evaluable\Compiler\Context\PrefixOperatorDescriptor;
use BrainDiminished\Evaluable\Evaluable;
use BrainDiminished\Evaluable\Compiler\Exception\CompilationException;

/**
 * Compile a raw expression (string) into an instance of Evaluable, respecting the given compilation context.
 */
class EvaluableCompiler
{
    /** @var CompilationContext */
    protected $compilationContext;

    private const INITIAL_STATE = Token::ATOM | Token::PREFIX_OPERATOR;

    private const PROCESS_STATE = Token::INFIX_OPERATOR | Token::DELIMITER;

    public function __construct(CompilationContext $context)
    {
        $this->compilationContext = $context;
    }

    public function compile(string $expression): Evaluable
        {
        $stream = new CompilationStream($expression, $this->compilationContext);
        return $this->readExpression($stream);
    }

    protected function readExpression(CompilationStream $stream, $delimiters = null): Evaluable
    {
        /** @var Evaluable[] $processed */
        $processed = [];
        /** @var Token[] $stack */
        $stack = [];

        $state = self::INITIAL_STATE;
        while (!$stream->next($state, $delimiters) instanceof DelimiterToken) {
            switch ($state) {
                case self::INITIAL_STATE:
                    $state = $this->initialize($stream, $processed, $stack);
                    break;
                case self::PROCESS_STATE:
                    $state = $this->process($stream, $processed, $stack);
                    break;
            }
        }

        while(!empty($stack)) {
            $this->execute(array_pop($stack), $processed);
        }

        if (count($processed) > 1) {
            throw new CompilationException('Expression not valid', 0);
        }
        return $processed[0];
    }

    protected function initialize(CompilationStream $stream, array &$processed, array &$stack)
    {
        $token = $stream->current();
        switch (true) {
            case $token instanceof AtomToken:
                $this->execute($token, $processed);
                return self::PROCESS_STATE;
            case $token instanceof PrefixOperatorToken:
                return $this->processOperator($stream, $processed, $stack);
            default:
                throw new CompilationException("State error near $token->symbol", $token->position);
        }
    }

    protected function process(CompilationStream $stream, array &$processed, array &$stack)
    {
        $token = $stream->current();
        switch (true) {
            case $token instanceof InfixOperatorToken:
                return $this->processOperator($stream, $processed, $stack);
            default:
                throw new CompilationException("State error near $token->symbol", $token->position);
        }
    }

    protected function processOperator(CompilationStream $stream, array &$processed, array &$stack)
    {
        /** @var OperatorToken $token */
        $token = $stream->current();
        $descriptor = $token->descriptor;
        $arity = $descriptor->arity;

        $this->stack($token, $processed, $stack);

        if ($arity->isClosed($delimiter)) {
            $args = $this->readClosedArgs($stream, $delimiter);
        } else if ($arity->isFixed($argc)) {
            if ($argc === 0) {
                $args = [];
                array_pop($stack);
            } else if ($arity->isPlural($separator)) {
                for ($i = 1; $i < $argc; $i++) {
                    $processed[] = $this->readExpression($stream, $separator);
                }
                return self::INITIAL_STATE;
            } else {
                return self::INITIAL_STATE;
            }
        } else {
            throw new CompilationException("Operator `$token->symbol` should either accept a fixed number of arguments or end with a delimiter", $token->position);
        }

        array_pop($stack);
        switch (true) {
            case $descriptor instanceof PrefixOperatorDescriptor:
                $processed[] = $descriptor->instantiate($args);
                break;
            case $descriptor instanceof InfixOperatorDescriptor:
                $lhs = array_pop($processed);
                $processed[] = $descriptor->instantiate($lhs, $args);
                break;
        }
        return self::PROCESS_STATE;
    }

    protected function readClosedArgs(CompilationStream $stream, string $delimiter)
    {
        /** @var OperatorToken $token */
        $token = $stream->current();
        $arity = $token->descriptor->arity;

        if ($stream->tryNext(Token::DELIMITER, $delimiter)) {
            $args = [];
        } else if ($arity->isPlural($separator)) {
            $args = $this->readExpressionList($stream, $separator, $delimiter);
        } else {
            $args = [$this->readExpression($stream, $delimiter)];
        }

        if ($arity->isFixed($argc) && count($args) !== $argc) {
            $actual = count($args);
            throw new CompilationException("Operator `$token->symbol` expected $argc operands (got $actual instead)", $token->position);
        }

        if (!$arity->allowNoArg() && empty($args)) {
            throw new CompilationException("Missing operand after operator `$token->symbol`", $token->position);
        }

        return $args;
    }

    protected function readExpressionList(CompilationStream $stream, string $separator, string $delimiter)
    {
        if ($stream->tryNext(Token::DELIMITER, $delimiter)) {
            return [];
        }

        $args = [];
        do {
            $args[] = $this->readExpression($stream, [$separator, $delimiter]);
        } while($stream->current()->symbol !== $delimiter);

        return $args;
    }

    protected function stack(OperatorToken $token, array &$evaluables, array &$stack)
    {
        while (!empty($stack)
            && !$token->descriptor->precede(end($stack)->descriptor)) {
            $this->execute(array_pop($stack), $evaluables);
        }

        $stack[] = $token;
    }

    protected function execute(Token $token, array &$processed)
    {
        switch (true) {
            case $token instanceof AtomToken:
                $processed[] = $this->compilationContext->buildAtom($token->symbol);
                break;
            case $token instanceof OperatorToken:
                if (!$token->descriptor->arity->isFixed($argc)) {
                    throw new CompilationException("State error near $token->symbol", $token->position);
                }
                $args = [];
                if (count($processed) < $argc) {
                    throw new CompilationException('Unexpected end of expression', $token->position);
                }
                for($i = 0; $i < $argc; $i++) {
                    array_unshift($args, array_pop($processed));
                }
                /** @var PrefixOperatorDescriptor $descriptor */
                $descriptor = $token->descriptor;
                switch (true) {
                    case $descriptor instanceof InfixOperatorDescriptor:
                        $lhs = array_pop($processed);
                        $processed[] = $descriptor->instantiate($lhs, $args);
                        break;
                    case $descriptor instanceof PrefixOperatorDescriptor:
                        $processed[] = $descriptor->instantiate($args);
                        break;
                }
                break;
            default:
                throw new CompilationException("Unexpected token `$token->symbol` in stack", $token->position);
        }
    }
}
