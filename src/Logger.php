<?php

declare(strict_types=1);

namespace Jogger;

use DateTime;
use Exception;
use Psr\Log\{AbstractLogger, InvalidArgumentException, LoggerInterface, LogLevel, NullLogger};
use Jogger\Output\OutputPlugin;
use stdClass;

class Logger extends AbstractLogger implements LoggerInterface
{
    private stdClass $additionalFields;
    private stdClass $defaultAdditionalFields;
    private bool $isTimeFieldSet = false;
    private string $timeZone;
    private string $name;
    /**
     * @var array<OutputPlugin>
     */
    private array $outputs;

    /**
     * Logger constructor.
     * @param string $name
     * @param array<OutputPlugin> $outputs
     * @param string $timeZone
     */
    public function __construct(string $name, array $outputs, string $timeZone) {
        $this->name = $name;
        $this->timeZone = $timeZone;
        $this->outputs = $outputs;
    }

    /**
     * @param stdClass $defaultAdditionalFields
     */
    public function setDefaultAdditionalFields(stdClass $defaultAdditionalFields): void {
        $this->defaultAdditionalFields = $defaultAdditionalFields;
    }

    private function createLogLine(string $level, string $message): string {
        if (!$this->isTimeFieldSet) {
            $this->setTimeFormatUnix();
        }
        $this->additionalFields->level = $level;
        $this->additionalFields->message = $message;
        $mergedObject = (object)array_merge(
            (array)$this->defaultAdditionalFields,
            (array)$this->additionalFields
        );
        return json_encode($mergedObject, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function setTimeFormatUnix(): void {
        $this->defaultAdditionalFields->timestamp = new DateTime("U");
        $this->isTimeFieldSet = true;
    }

    public function setTimeFormatISO8601(): void {
        $this->defaultAdditionalFields->timestamp = new DateTime('Y-m-d\TH:i:s.uP');
        $this->isTimeFieldSet = true;
    }

    public function addString(string $key, string $value): Logger {
        $this->additionalFields->$key = $value;
        return $this;
    }

    public function addInteger(string $key, int $value): Logger {
        $this->additionalFields->$key = $value;
        return $this;
    }

    public function addFloat(string $key, float $value): Logger {
        $this->additionalFields->$key = $value;
        return $this;
    }

    public function addBoolean(string $key, bool $value): Logger {
        $this->additionalFields->$key = $value;
        return $this;
    }

    public function addArray(string $key, array $value): Logger {
        $this->additionalFields->$key = $value;
        return $this;
    }

    public function addException(string $key, Exception $value): Logger {
        $dummyObject = new stdClass();
        $dummyObject->code = $value->getCode();
        $dummyObject->message = $value->getMessage();
        $dummyObject->file = $value->getFile();
        $dummyObject->line = $value->getLine();
        $dummyObject->trace = $value->getTraceAsString();
        $this->additionalFields->$key = $dummyObject;
        return $this;
    }

    /**
     * @param mixed $level
     * @param $message
     * @param array $context
     * @throws InvalidArgumentException
     */
    public function log($level, $message, array $context = array()): void {
        Utilities::validateLoglevel($level);
        $interpolate = new Interpolate();
        $logLine = $this->createLogLine($level, $interpolate($message, $context));
        foreach ($this->outputs as $output) {
            $output->write($message);
            $output->rewind();
        }
    }
}
