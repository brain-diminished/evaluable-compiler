<?php
namespace BrainDiminished\Compiler;

use BrainDiminished\Compiler\Environment\CompilationEnvironment;
use BrainDiminished\Compiler\Exception\CompilationException;
use BrainDiminished\Compiler\Stream\CompilationStream;
use BrainDiminished\Compiler\Atom\DelimiterAtom;
use BrainDiminished\Compiler\Atom\OperatorAtom;
use BrainDiminished\Compiler\Atom\Atom;
use BrainDiminished\Evaluable\Evaluable;

/**
 * Compile a raw expression (string) into an instance of Evaluable, following the rules provided by a compilation context.
 */
final class Compiler
{
    /** @var CompilationEnvironment */
    private $compilationContext;

    private const INITIAL_STATE = Atom::PREFIX_OPERATOR;
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

    private function compileOne(CompilationStream $stream, $delimiters = null)
    {
        /** @var Evaluable[] $processed */
        $processed = [];

        /** @var Atom[] $stack */
        $stack = [];

        $state = self::INITIAL_STATE;
        while (!$stream->next($state, $delimiters) instanceof DelimiterAtom) {
            $state = $this->processAtom($stream, $processed, $stack);
        }

        while(!empty($stack)) {
            $this->queueAtom(array_pop($stack), $processed);
        }

        if (count($processed) > 1) {
            throw new CompilationException('Unexpected end of expression', $stream->position());
        }
        return $processed[0];
    }

    private function processAtom(CompilationStream $stream, array &$processed, array &$stack): int
    {
        /** @var OperatorAtom $atom */
        $atom = $stream->current();
        $arity = $atom->meta()->arity();

        $this->stack($atom, $processed, $stack);

        $rargc = $atom->rargc();
        if ($rargc === 0) {
            $args = [];
        } else if (!empty($atom->meta()->delimiter())) {
            if ($rargc === 1) {
                $args = [$this->compileOne($stream, $atom->meta()->delimiter())];
            } else {
                $args = $this->compileMany($stream, $atom->meta()->separator(), $atom->meta()->delimiter(), $rargc);
            }
        } else /* empty($arity->delimiter()) => $rargc !== null */ {
            for ($i = 1; $i < $rargc; $i++) {
                $processed[] = $this->compileOne($stream, $atom->meta()->separator());
            }
            return self::INITIAL_STATE;
        }

        array_pop($stack);
        for ($i = 0; $i < $atom->largc(); $i++) {
            array_unshift($args, $this->popOne($processed));
        }
        $processed[] = $atom->meta()->build($args);
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

    private function stack(OperatorAtom $atom, array &$processed, array &$stack)
    {
        while (!empty($stack)
            && !$this->precede($atom, end($stack))) {
            $this->queueAtom(array_pop($stack), $processed);
        }

        $stack[] = $atom;
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
        if ($o1->largc() === 0) {
            return true;
        }
        if ($o2->rargc() === 0 || !empty($o2->meta()->arity()->delimiter())) {
            return false;
        }
        return $o1->meta()->arity()->leftPriority() < $o2->meta()->arity()->rightPriority();
    }

    private function queueAtom(OperatorAtom $atom, array &$processed)
    {
        $processed[] = $atom->meta()->build($this->popMany($processed, $atom->argc()));
    }
}
