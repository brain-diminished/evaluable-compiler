<?php
namespace BrainDiminished\Evaluable\Compiler\Stream;

use BrainDiminished\Evaluable\Compiler\Exception\CompilationException;
use BrainDiminished\Evaluable\Compiler\Token\AtomToken;
use BrainDiminished\Evaluable\Compiler\Token\DelimiterToken;
use BrainDiminished\Evaluable\Compiler\Token\InfixOperatorToken;
use BrainDiminished\Evaluable\Compiler\Token\PrefixOperatorToken;
use BrainDiminished\Evaluable\Compiler\Token\Token;
use BrainDiminished\Evaluable\Compiler\Context\CompilationContext;

class CompilationStream
{
    /** @var CompilationContext */
    protected $context;

    /** @var string */
    protected $expression;

    /** @var string */
    protected $patternDelimiter;

    /** @var string */
    protected $atomPattern;

    /** @var string */
    protected $prefixPattern;

    /** @var string */
    protected $infixPattern;

    /** @var string */
    protected $stream;

    /** @var Token */
    protected $lastToken = null;


    public function __construct(string $expression, CompilationContext $context, string $safetyChar = '@')
    {
        $this->context = $context;

        $this->patternDelimiter = $this->context->getSafetyChar();
        $this->atomPattern = $this->context->getAtomPattern();
        $this->prefixPattern = $this->context->getPrefixOperatorPattern();
        $this->infixPattern = $this->context->getInfixOperatorPattern();

        $this->expression = $expression;
        $this->stream = ltrim($expression);
    }

    public function current(): ?Token
    {
        return $this->lastToken;
    }

    public function position(): int
    {
        return strlen($this->expression) - strlen($this->stream);
    }

    public function next(int $flags, $delimiters = null): ?Token
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
        if ($flags & Token::ATOM
            && $this->tryRead($this->atomPattern, $symbol)) {
            $this->lastToken = new AtomToken($symbol, $position);
            return true;
        }
        if ($flags & Token::PREFIX_OPERATOR
            && $this->tryRead($this->prefixPattern, $symbol)) {
            $this->lastToken = new PrefixOperatorToken($symbol, $position, $this->context->getPrefixOperatorDescriptor($symbol));
            return true;
        }
        if ($flags & Token::INFIX_OPERATOR
            && $this->tryRead($this->infixPattern, $symbol)) {
            $this->lastToken = new InfixOperatorToken($symbol, $position, $this->context->getInfixOperatorDescriptor($symbol));
            return true;
        }
        if ($flags & Token::DELIMITER
            && $this->tryRead($this->getDelimiterPattern($delimiters), $symbol)) {
                $this->lastToken = new DelimiterToken($symbol, $position);
            return true;
        }

        return false;
    }

    protected function tryRead($pattern, string &$symbol = null): bool
    {
        if (empty($pattern)) {
            return false;
        }

        $regex = "$this->patternDelimiter^($pattern)$this->patternDelimiter";
        if (preg_match($regex, $this->stream, $matches)) {
            $symbol = $matches[0];
            $this->stream = substr($this->stream, strlen($symbol));
            $this->stream = ltrim($this->stream);
            return true;
        }

        return false;
    }

    protected function getDelimiterPattern($delimiters) {
        if (empty($delimiters)) {
            return '$';
        } else  {
            return $this->pattern($delimiters);
        }
    }

    protected function pattern($symbols)
    {
        if (is_array($symbols)) {
            return implode('|', array_map('preg_quote', array_filter($symbols)));
        } else {
            return preg_quote($symbols);
        }
    }
}
