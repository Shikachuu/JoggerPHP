<?php

declare(strict_types=1);

namespace Jogger\Output;

use Jogger\Utilities;

/**
 * Base Output class, contains all and only necessary methods and properties to create output instances for Jogger.
 * @package Jogger\Output
 */
abstract class BaseOutput implements OutputPlugin
{
    protected string $level;

    /**
     * BaseOutput constructor.
     * @param string $level Minimum log level of the Output instance.
     */
    public function __construct(string $level) {
        $this->level = $level;
    }

    /**
     * Write the give message to its underlying storage.
     * @codeCoverageIgnore
     * @param string $message Log message that should be written.
     */
    public function write(string $message): void {
        // NOOP
    }

    /**
     * Rewind the given output's pointer to put the next log message for example to the top of the output file.
     * @codeCoverageIgnore
     */
    public function rewind(): void {
        // NOOP
    }

    /**
     * Closes the output's underlying storage.
     * @codeCoverageIgnore
     */
    public function close(): bool {
        return true;
    }

    /**
     * Sets the level of the Output instance.
     * @param string $level Minimum log level of the Output instance.
     */
    public function setLevel(string $level): void {
        Utilities::validateLoglevel($level);
        $this->level = $level;
    }

    /**
     * Returns the current minimum level of the Output instance.
     * @return string Current log level.
     */
    public function getLevel(): string {
        return $this->level;
    }
}
