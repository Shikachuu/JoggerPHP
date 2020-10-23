<?php

declare(strict_types=1);

namespace Jogger;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

final class Utilities
{
    /**
     * Validates loglevel
     * @param string $level
     * @throws InvalidArgumentException
     */
    public static function validateLoglevel(string $level): void {
        $logLevels = [
            LogLevel::ALERT,
            LogLevel::DEBUG,
            LogLevel::CRITICAL,
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
            LogLevel::NOTICE,
            LogLevel::WARNING,
            LogLevel::INFO
        ];
        if (!in_array(trim(strtolower($level)), $logLevels)) {
            throw new InvalidArgumentException("Invalid log level was provided.");
        }
    }
}
