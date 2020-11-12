<?php

namespace Jogger\Output;

/**
 * No operation output gives the possibility, to use the logger instance without actually writing messages.
 * @package Jogger\Output
 */
class NoopOutput extends BaseOutput implements OutputPlugin
{
    /**
     * @inheritDoc
     * @param string $level
     */
    public function __construct(string $level) {
        parent::__construct($level);
    }
}
