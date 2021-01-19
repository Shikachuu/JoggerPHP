<?php

namespace Jogger\Output;

/**
 * Output plugin for PHP streams.
 * @package Jogger\Output
 */
class StreamOutput extends BaseOutput implements OutputPlugin
{
    /**
     * @var resource|null|closed-resource $stream
     */
    private $stream;

    /**
     * @param string $level Minimum log level of the Output instance.
     * @param string $streamName Link of the PHP php stream. (php://output) for example.
     */
    public function __construct(string $level, string $streamName) {
        parent::__construct($level);
        $this->stream = fopen($streamName, "a+");
    }

    /**
     * @inheritDoc
     */
    public function write(string $message): void {
        /**
         * @var resource $this- >stream
         */
        fwrite($this->stream, $message);
    }

    /**
     * @inheritDoc
     */
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
