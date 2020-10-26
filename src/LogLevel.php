<?php

namespace Jogger;

/**
 * Class LogLevel
 * @package Jogger
 * @codeCoverageIgnore
 */
class LogLevel extends \Psr\Log\LogLevel
{
    public const EMERGENCY = 800;
    public const ALERT = 700;
    public const CRITICAL = 600;
    public const ERROR = 500;
    public const WARNING = 400;
    public const NOTICE = 300;
    public const INFO = 200;
    public const DEBUG = 100;
}
