<?php
namespace BrainDiminished\Compiler\Symbol;

class SymbolPool
{
    /** @var Symbol[] */
    private $symbols = [];

    /** @var string */
    private $pattern;

    /**
     * SymbolPool constructor.
     * @param Symbol[] $symbols
     */
    public function __construct(array $symbols = [])
    {
        $this->symbols = [];
        $patterns = [];
        $i = 0;
        foreach ($symbols as $symbol) {
            $index = "index_$i";
            $pattern = $symbol->pattern();
            $this->symbols[$index] = $symbol;
            $patterns[] = "(?<$index>$pattern)";
            $i++;
        }
        $this->pattern = implode('|', $patterns);
    }

    public function add(Symbol $symbol)
    {
        $i = count($this->symbols);
        $index = "index_$i";
        $this->symbols[$index] = $symbol;
        $pattern = $symbol->pattern();
        $pattern = "(?<$index>$pattern)";
        $this->pattern = empty($this->pattern) ? $pattern : "$this->pattern|$pattern";
    }

    public function preg(string $subject, Symbol &$symbol = null, string &$match = null): bool
    {
        if (empty($this->pattern)) {
            return false;
        }

        if (!preg_match("(^($this->pattern))", $subject, $matches)) {
            return false;
        }

        $match = $matches[0];
        $len = strlen($match);
        foreach ($matches as $key => $value) {
            if (strlen($value) !== $len
                || is_int($key)
                || !key_exists($key, $this->symbols)) {
                continue;
            }
            $symbol =  $this->symbols[$key];
            return true;
        }
        throw new \Exception("Could not identify $match in symbol pool: search for errors in symbol patterns");
    }
}
