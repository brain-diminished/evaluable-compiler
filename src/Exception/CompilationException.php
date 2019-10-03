<?php
namespace BrainDiminished\Compiler\Exception;

class CompilationException extends \Exception
{
    /** @var int */
    protected $position;

    public function __construct(string $message, int $position, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }
}
