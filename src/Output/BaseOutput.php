<?php

declare(strict_types=1);

namespace Jogger\Output;

use Jogger\Utilities;

abstract class BaseOutput implements OutputPlugin
{
    protected string $level;

    public function __construct(string $level) {
        $this->level = $level;
    }

    /**
     * @codeCoverageIgnore
     * @param string $message
     */
    public function write(string $message): void {
        // NOOP
    }

    /**
     * @codeCoverageIgnore
     */
    public function rewind(): void {
        // NOOP
    }

    /**
     * @codeCoverageIgnore
     */
    public function close(): void {
        // NOOP
    }

    public function setLevel(string $level): void {
        Utilities::validateLoglevel($level);
        $this->level = $level;
    }

    public function getLevel(): string {
        return $this->level;
    }
}
