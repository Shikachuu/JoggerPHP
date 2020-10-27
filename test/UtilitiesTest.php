<?php /** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Jogger;

use PHPUnit\Framework\TestCase;

/**
 * Class UtilitiesTest
 * @covers \Jogger\Utilities
 */
class UtilitiesTest extends TestCase
{
    /**
     * @covers \Jogger\Utilities::validateLoglevel
     */
    public function testValidateLogLevel() {
        $invalidLevels = ["you", "shall", "not", "emmit", "any", "fatal", "case"];
        $validLevels = ["alert", "debug", "critical", "emergency", "error", "notice", "warning", "info"];
        foreach ($invalidLevels as $invalidLevel) {
            $this->expectException("InvalidArgumentException");
            $this->expectExceptionMessage("Invalid log level was provided.");
            Utilities::validateLoglevel($invalidLevel);
        }
        foreach ($validLevels as $validLevel) {
            Utilities::validateLoglevel($validLevel);
        }
    }
}
