<?php


namespace Output;


use Jogger\Output\BaseOutput;
use Jogger\Output\OutputPlugin;

class ArrayOutput extends BaseOutput implements OutputPlugin
{
    private array $logs = [];

    public function write(string $message): void {
        array_push($this->logs, $message);
    }

    public function getLastLogLine(): string {
        return $this->logs[count($this->logs) - 1];
    }

    public function getAllLogLines(): array {
        return $this->logs;
    }

    public function getNumberOfMessages(): int {
        return count($this->logs);
    }

}