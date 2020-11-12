<?php

declare(strict_types=1);

namespace Jogger\Output;

/**
 * PHP STDOUT Output plugin based on the StreamOutput plugin.
 * @package Jogger\Output
 */
final class StdoutOutput extends StreamOutput implements OutputPlugin
{
    /**
     * @param string $level Minimum log level of the Output instance.
     */
    public function __construct(string $level) {
        parent::__construct($level, "php://stdout");
    }
}
