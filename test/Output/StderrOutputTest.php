<?php /** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Output;

use Jogger\Output\StderrOutput;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class StderrOutputTest
 * @covers \Jogger\Output\StderrOutput
 * @uses \Jogger\Output\BaseOutput
 * @uses \Jogger\Output\StreamOutput
 */
class StderrOutputTest extends TestCase
{
    /**
     * @covers \Jogger\Output\StderrOutput::__construct
     */
    public function test__construct() {
        $stderr = new StderrOutput("info");
        $reflector = new ReflectionClass($stderr);
        $level = $reflector->getProperty("level");
        $level->setAccessible(true);
        $this->assertEquals("info", $level->getValue($stderr));
    }
}
