<?php

namespace Jogger\Output;

class NoopOutput extends BaseOutput implements OutputPlugin
{
    public function __construct(string $level) {
        parent::__construct($level);
    }
}
