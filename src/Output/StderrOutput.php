<?php

declare(strict_types=1);

namespace Jogger\Output;

final class StderrOutput extends StreamOutput implements OutputPlugin
{
    public function __construct(string $level) {
        parent::__construct($level, "php://stderr");
    }
}
