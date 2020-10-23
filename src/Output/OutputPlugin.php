<?php

declare(strict_types=1);

namespace Jogger\Output;

interface OutputPlugin
{
    public function write(string $message): void;

    public function rewind(): void;

    public function close(): void;

    public function setLevel(string $level): void;

    public function getLevel(): string;
}
