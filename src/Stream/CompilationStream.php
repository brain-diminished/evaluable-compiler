<?php
namespace BrainDiminished\Compiler\Stream;

use BrainDiminished\Compiler\Exception\CompilationException;
use BrainDiminished\Compiler\Atom\KeywordAtom;
use BrainDiminished\Compiler\Atom\DelimiterAtom;
use BrainDiminished\Compiler\Atom\InfixAtom;
use BrainDiminished\Compiler\Atom\PrefixSymbol;
use BrainDiminished\Compiler\Atom\Atom;
use BrainDiminished\Compiler\Environment\CompilationEnvironment;

final class CompilationStream
{
    /** @var CompilationEnvironment */
    private $context;

    /** @var string */
    private $expression;

    /** @var string */
    private $stream;

    /** @var Atom */
    private $lastToken = null;


    public function __construct(string $expression, CompilationEnvironment $context, string $safetyChar = '@')
    {
        $this->context = $context;

        $this->expression = $expression;
        $this->stream = $expression;
        $this->ltrim();
    }

    public function current(): ?Atom
    {
        return $this->lastToken;
    }

    public function position(): int
    {
        return strlen($this->expression) - strlen($this->stream);
    }

    public function next(int $flags, $delimiters = null): ?Atom
    {
        if ($this->tryNext($flags, $delimiters)) {
            return $this->lastToken;
        } else if (empty($this->stream)) {
            throw new CompilationException("Unexpected end of expression, you may want to check your parentheses", $this->position());
        } else {
            $extract = strlen($this->stream) < 13 ? $this->stream : substr($this->stream, 0, 10).'...';
            throw new CompilationException("Unexpected token near $extract", $this->position());
        }
    }

    public function tryNext(int $flags, $delimiters = null): bool
    {
        $position = $this->position();
        if ($flags & Atom::KEYWORD
            && $this->tryRead($this->context->getAtomPattern(), $symbol)) {
            $this->lastToken = new KeywordAtom($symbol, $position);
            return true;
        }
        if ($flags & Atom::PREFIX_OPERATOR
            && $this->tryRead($this->context->getPrefixOperatorPattern(), $symbol)) {
            $this->lastToken = new PrefixSymbol($symbol, $position, $this->context->getPrefixOperator($symbol));
            return true;
        }
        if ($flags & Atom::INFIX_OPERATOR
            && $this->tryRead($this->context->getInfixOperatorPattern(), $symbol)) {
            $this->lastToken = new InfixAtom($symbol, $position, $this->context->getInfixOperator($symbol));
            return true;
        }
        if ($flags & Atom::DELIMITER
            && $this->tryRead($this->getDelimiterPattern($delimiters), $symbol)) {
                $this->lastToken = new DelimiterAtom($symbol, $position);
            return true;
        }

        return false;
    }

    private function tryRead($pattern, string &$symbol = null): bool
    {
        if (empty($pattern)) {
            return false;
        }
        if (preg_match("(^($pattern))", $this->stream, $matches)) {
            $symbol = $matches[0];
            $this->stream = substr($this->stream, strlen($symbol));
            $this->ltrim();
            return true;
        }

        return false;
    }

    private function getDelimiterPattern($delimiters) {
        if (empty($delimiters)) {
            return '$';
        } else  {
            return $this->pattern($delimiters);
        }
    }

    private function pattern($symbols)
    {
        if (is_array($symbols)) {
            return implode('|', array_map('preg_quote', array_filter($symbols)));
        } else {
            return preg_quote($symbols);
        }
    }

    private function ltrim()
    {
        $blank = $this->context->getBlankPattern();
        if (preg_match("(^($blank))", $this->stream, $matches)) {
            $this->stream = substr($this->stream, strlen($matches[0]));
        }
    }
}
