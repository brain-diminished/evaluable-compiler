<?php
namespace BrainDiminished\Compiler;

use BrainDiminished\Compiler\Environment\CompilationEnvironment;
use BrainDiminished\Compiler\Exception\CompilationException;
use BrainDiminished\Compiler\Stream\CompilationStream;
use BrainDiminished\Compiler\Atom\KeywordAtom;
use BrainDiminished\Compiler\Atom\DelimiterAtom;
use BrainDiminished\Compiler\Atom\OperatorAtom;
use BrainDiminished\Compiler\Atom\PrefixSymbol;
use BrainDiminished\Compiler\Atom\Atom;
use BrainDiminished\Evaluable\Evaluable;

/**
 * Compile a raw expression (string) into an instance of Evaluable, following the rules provided by a compilation context.
 */
final class Compiler
{
    /** @var CompilationEnvironment */
    private $compilationContext;

    private const INITIAL_STATE = Atom::KEYWORD | Atom::PREFIX_OPERATOR;
    private const PROCESS_STATE = Atom::INFIX_OPERATOR | Atom::DELIMITER;

    public function __construct(CompilationEnvironment $context)
    {
        $this->compilationContext = $context;
    }

    public function compile(string $expression): Evaluable
        {
        $stream = new CompilationStream($expression, $this->compilationContext);
        return $this->compileOne($stream);
    }

    private function compileOne(CompilationStream $stream, $delimiters = null): Evaluable
    {
        /** @var Evaluable[] $processed */
        $processed = [];

        /** @var Atom[] $stack */
        $stack = [];

        $state = self::INITIAL_STATE;
        while (!$stream->next($state, $delimiters) instanceof DelimiterAtom) {
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
            $this->interpret(array_pop($stack), $processed);
        }

        if (count($processed) > 1) {
            throw new CompilationException('Unexpected end of expression', $stream->position());
        }
        return $processed[0];
    }

    private function initialize(CompilationStream $stream, array &$processed, array &$stack): int
    {
        $token = $stream->current();
        switch (true) {
            case $token instanceof KeywordAtom:
                $this->interpret($token, $processed);
                return self::PROCESS_STATE;
            case $token instanceof PrefixSymbol:
                return $this->process($stream, $processed, $stack);
            default:
                throw new CompilationException("State error near $token->symbol", $token->position);
        }
    }

    private function process(CompilationStream $stream, array &$processed, array &$stack): int
    {
        /** @var OperatorAtom $token */
        $token = $stream->current();
        $arity = $token->meta->arity();

        $this->stack($token, $processed, $stack);

        $largc = $token->largc();
        if ($largc === 0) {
            $args = [];
        } else if (!empty($arity->delimiter())) {
            if ($largc === 1) {
                $args = [$this->compileOne($stream, $arity->delimiter())];
            } else {
                $args = $this->compileMany($stream, $arity->separator(), $arity->delimiter(), $largc);
            }
        } else /* $largc !== null */ {
            for ($i = 1; $i < $largc; $i++) {
                $processed[] = $this->compileOne($stream, $arity->separator());
            }
            return self::INITIAL_STATE;
        }

        array_pop($stack);
        for ($i = $largc; $i < $arity->argc(); $i++) {
            array_unshift($args, $this->popOne($processed));
        }
        $processed[] = $token->meta->build($args);
        return self::PROCESS_STATE;
    }

    private function compileMany(CompilationStream $stream, string $separator, string $delimiter, ?int $expected = null): array
    {
        $opening = $stream->current();
        if ($stream->tryNext(Atom::DELIMITER, $delimiter)) {
            $args = [];
        } else {
            do {
                $args[] = $this->compileOne($stream, [$separator, $delimiter]);
            } while($stream->current()->symbol !== $delimiter);
        }

        if ($expected !== null && count($args) !== $expected) {
            throw new CompilationException("Wrong number of operands: expected $expected, got ".count($args), $opening->position);
        }

        return $args;
    }

    private function stack(OperatorAtom $token, array &$processed, array &$stack)
    {
        while (!empty($stack)
            && !$this->precede($token, end($stack))) {
            $this->interpret(array_pop($stack), $processed);
        }

        $stack[] = $token;
    }

    private function popOne(array &$processed): Evaluable
    {
        if (empty($processed)) {
            throw new CompilationException('Missing operands', -1);
        }
        return array_pop($processed);
    }

    private function popMany(array &$processed ,int $n): array
    {
        if (count($processed) < $n) {
            throw new CompilationException('Missing operands', -1);
        }
        $popped = [];
        for($i = 0; $i < $n; $i++) {
            array_unshift($popped, array_pop($processed));
        }
        return $popped;
    }

    private function precede(OperatorAtom $o1, OperatorAtom $o2): bool
    {
        $a1 = $o1->meta->arity();
        if ($a1->isPrefix()) {
            return true;
        }
        $a2 = $o2->meta->arity();
        if ($a2->argc() === 1 || !empty($a2->delimiter())) {
            return false;
        }
        return $a1->leftPriority() < $a2->rightPriority();
    }

    private function interpret(Atom $token, array &$processed)
    {
        switch (true) {
            case $token instanceof KeywordAtom:
                $processed[] = $this->compilationContext->buildAtom($token);
                break;
            case $token instanceof OperatorAtom:
                $args = $this->popMany($processed, $token->meta->arity()->argc());
                $processed[] = $token->meta->build($args);
                break;
        }
    }
}
