<?php

namespace Jogger\Output;

class StreamOutput extends BaseOutput implements OutputPlugin
{
    private $stream;

    public function __construct(string $level, string $streamName) {
        parent::__construct($level);
        $this->stream = fopen($streamName, "w");
    }

    public function write(string $message): void {
        fwrite($this->stream, $message);
    }

    public function close(): void {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
    }

    public function __destruct() {
        $this->close();
    }
}
