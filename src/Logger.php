<?php

declare(strict_types=1);

namespace Jogger;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Jogger\Output\NoopOutput;
use Jogger\Output\OutputPlugin;
use Psr\Log\{AbstractLogger, InvalidArgumentException, LoggerInterface, LogLevel};
use stdClass;

class Logger extends AbstractLogger implements LoggerInterface
{
    private stdClass $dynamicFields;
    private stdClass $staticFields;
    private string $timeFieldFormat = "";
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
    public function __construct(string $name, array $outputs = array(), string $timeZone = "Europe/London") {
        $this->name = $name;
        $this->timeZone = $timeZone;
        $this->dynamicFields = new stdClass();
        $this->staticFields = new stdClass();
        if ($outputs === array()) {
            $this->outputs = [new NoopOutput(LogLevel::DEBUG)];
        } else {
            $this->outputs = $outputs;
        }
    }

    /**
     * @param stdClass $staticFields
     */
    public function setStaticFields(stdClass $staticFields): void {
        $this->staticFields = $staticFields;
    }

    /**
     * @param string $level level of the logging line
     * @param string $message interpolated log message
     * @return string Json string with the inserted fields
     * @throws Exception
     */
    private function createLogLine(string $level, string $message): string {
        if ($this->timeFieldFormat === "") {
            $this->setTimeFormatUnix();
        }
        $baseProperties = new stdClass();
        $timestamp = new DateTimeImmutable("now", new DateTimeZone($this->timeZone));
        $baseProperties->timestamp = $timestamp->format($this->timeFieldFormat);
        $baseProperties->level = $level;
        $baseProperties->message = $message;
        $mergedObject = (object)array_merge(
            (array)$baseProperties,
            (array)$this->staticFields,
            (array)$this->dynamicFields
        );
        return json_encode($mergedObject, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    /**
     * Sets the logger instance's time format to Unix timestamp
     */
    public function setTimeFormatUnix(): void {
        $this->timeFieldFormat = "U";
    }

    /**
     * Sets the logger instance's time format to ISO8601
     */
    public function setTimeFormatISO8601(): void {
        $this->timeFieldFormat = 'Y-m-d\TH:i:s.uP';
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function addString(string $key, string $value): Logger {
        $this->dynamicFields->$key = $value;
        return $this;
    }

    public function addInteger(string $key, int $value): Logger {
        $this->dynamicFields->$key = $value;
        return $this;
    }

    public function addFloat(string $key, float $value): Logger {
        $this->dynamicFields->$key = $value;
        return $this;
    }

    public function addBoolean(string $key, bool $value): Logger {
        $this->dynamicFields->$key = $value;
        return $this;
    }

    public function addArray(string $key, array $value): Logger {
        $this->dynamicFields->$key = $value;
        return $this;
    }

    public function addException(string $key, Exception $value): Logger {
        $dummyObject = new stdClass();
        $dummyObject->code = $value->getCode();
        $dummyObject->message = $value->getMessage();
        $dummyObject->file = $value->getFile();
        $dummyObject->line = $value->getLine();
        $dummyObject->trace = $value->getTraceAsString();
        $this->dynamicFields->$key = $dummyObject;
        return $this;
    }

    private function logLevelToNumber(string $level): int {
        Utilities::validateLoglevel($level);
        $logLevels = [
            LogLevel::ALERT => 800,
            LogLevel::EMERGENCY => 700,
            LogLevel::CRITICAL => 600,
            LogLevel::ERROR => 500,
            LogLevel::WARNING => 400,
            LogLevel::NOTICE => 300,
            LogLevel::INFO => 200,
            LogLevel::DEBUG => 100
        ];
        return $logLevels[$level];
    }

    /**
     * @param mixed $level
     * @param $message
     * @param array $context
     * @throws InvalidArgumentException|Exception
     */
    public function log($level, $message, array $context = array()): void {
        Utilities::validateLoglevel($level);
        $interpolate = new Interpolate();
        $logLine = $this->createLogLine($level, $interpolate($message, $context));
        foreach ($this->outputs as $output) {
            if ($this->logLevelToNumber($output->getLevel()) >= $this->logLevelToNumber($level)) {
                $output->rewind();
                $output->write($logLine);
            }
        }
    }
}
