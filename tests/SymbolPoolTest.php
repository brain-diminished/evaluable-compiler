<?php
namespace BrainDiminished\Test\Compiler\Symbol;

use BrainDiminished\Compiler\Symbol\SimpleSymbol;
use BrainDiminished\Compiler\Symbol\SymbolPool;
use PHPUnit\Framework\TestCase;

class SymbolPoolTest extends TestCase
{
    private $symbolPool;
    private $symbols;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->symbols = [
            new SimpleSymbol('('),
            new SimpleSymbol(')'),
            new SimpleSymbol('['),
            new SimpleSymbol(']'),
            new SimpleSymbol(','),
            new SimpleSymbol(';'),
        ];
        $this->symbolPool = new SymbolPool($this->symbols);
    }

    public function test()
    {
        $this->assertTrue($this->symbolPool->preg('(', $symbol));
        $this->assertEquals($this->symbols[0], $symbol);
        $this->assertTrue($this->symbolPool->preg(',   ', $symbol));
        $this->assertEquals($this->symbols[4], $symbol);
        $this->assertFalse($this->symbolPool->preg('  ('));
        $this->assertTrue($this->symbolPool->preg(']]'));
    }
}
