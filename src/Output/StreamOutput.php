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

    public function close(): bool {
        if (is_resource($this->stream)) {
            return fclose($this->stream);
        }
        $this->stream = null;
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function __destruct() {
        $this->close();
    }
}
