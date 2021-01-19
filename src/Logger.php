<?php

declare(strict_types=1);

namespace Jogger;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Jogger\Output\NoopOutput;
use Jogger\Output\OutputPlugin;
use Psr\Log\{AbstractLogger, InvalidArgumentException, LoggerInterface, LogLevel};
use ReflectionClass;
use stdClass;

/**
 * Jogger's main logging driver
 * @package Jogger
 */
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
     * Creates a new logging instance.
     * @param string $name name of the logging context
     * @param array<OutputPlugin> $outputs array of Output\OutputPlugin instances
     * @param string $timeZone timezone you want to use for the logger
     * @see https://www.php.net/manual/en/timezones.php Available Time Zones
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
     * @param array<mixed> $staticFields Key-Value paris (associative array)
     * Fields that should be included in every log line across the current logging instance.
     * May be overwritten by dynamically assigned fields.
     */
    public function setStaticFields(array $staticFields): void {
        $this->staticFields = (object)$staticFields;
    }

    /**
     * @param string $level Level of the log message
     * @param string $message Interpolated log message
     * @return string Json string with the inserted fields
     * @throws Exception On date time error
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
     * Adds a new string field to the log line and returns the current logging context.
     * @param string $key Name of the field
     * @param string $value Value of the field
     * @return $this Returns the current logging context
     */
    public function addString(string $key, string $value): Logger {
        $this->dynamicFields->$key = strval($value);
        return $this;
    }

    /**
     * Adds a new integer field to the log line and returns the current logging context.
     * @param string $key Name of the field
     * @param int $value Value of the field
     * @return $this Returns the current logging context
     */
    public function addInteger(string $key, int $value): Logger {
        $this->dynamicFields->$key = intval($value);
        return $this;
    }

    /**
     * Adds a new float field to the log line and returns the current logging context.
     * @param string $key Name of the field
     * @param float $value Value of the field
     * @return $this Returns the current logging context
     */
    public function addFloat(string $key, float $value): Logger {
        $this->dynamicFields->$key = floatval($value);
        return $this;
    }

    /**
     * Adds a new boolean field to the log line and returns the current logging context.
     * @param string $key Name of the field
     * @param bool $value Value of the field
     * @return $this Returns the current logging context
     */
    public function addBoolean(string $key, bool $value): Logger {
        $this->dynamicFields->$key = boolval($value);
        return $this;
    }

    /**
     * Adds a new array/object field to the log line,
     * depending on the passed array, and returns the current logging context.
     * @param string $key Name of the field
     * @param array $value Value of the field, associative arrays will be casted to Object
     * @return $this Returns the current logging context
     */
    public function addArray(string $key, array $value): Logger {
        $this->dynamicFields->$key = $value;
        return $this;
    }

    /**
     * Adds a new object field to the log line, based on the passed exception, and returns the current logging context.
     *
     * The Exception should be caught before passed in!
     * @param string $key Name of the field
     * @param Exception $value Value of the field, exception will be casted to Object with it's default primary fields
     * @return $this Returns the current logging context
     */
    public function addException(string $key, Exception $value): Logger {
        $dummyObject = new stdClass();
        $dummyObject->exception = (new ReflectionClass($value))->getShortName();
        $dummyObject->code = $value->getCode();
        $dummyObject->message = $value->getMessage();
        $dummyObject->file = $value->getFile();
        $dummyObject->line = $value->getLine();
        $dummyObject->trace = $value->getTraceAsString();
        $this->dynamicFields->$key = $dummyObject;
        return $this;
    }

    /**
     * @param string $level LogLevel that should be transformed to its weight.
     * @return int Returns the current weight of the log level.
     * @throws InvalidArgumentException On invalid LogLevel string input.
     */
    private static function logLevelToNumber(string $level): int {
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
     * Resets the dynamic fields.
     */
    private function resetDynamicFields(): void {
        $this->dynamicFields = new stdClass();
    }

    /**
     * @param string $level Level of the current log message.
     * Valid levels: debug, info, notice, warning, error, critical, emergency, alert
     * @psalm-suppress MoreSpecificImplementedParamType
     * @param string $message Actual log message, every word surrounded by curly braces,
     * will be replaced to its value pair from the $context array if presented.
     * @param array $context Context holds key-value pairs
     * to replace parts of the $message that are surrounded by curly braces and equals to a key.
     * @throws InvalidArgumentException|Exception Throws Exception when invalid log level or time zone is provided.
     * @see LogLevel For valid log levels as constants.
     */
    public function log($level, $message, array $context = array()): void {
        Utilities::validateLoglevel($level);
        $interpolate = new Interpolate();
        $logLine = $this->createLogLine($level, $interpolate($message, $context));
        $this->resetDynamicFields();
        foreach ($this->outputs as $output) {
            if (Logger::logLevelToNumber($output->getLevel()) <= Logger::logLevelToNumber($level)) {
                $output->rewind();
                $output->write($logLine . "\n");
            }
        }
    }
}
