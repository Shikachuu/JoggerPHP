<?php /** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Output;

use Jogger\Output\StdoutOutput;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class StdoutOutputTest
 * @covers \Jogger\Output\StdoutOutput
 * @uses   \Jogger\Output\BaseOutput
 * @uses   \Jogger\Output\StreamOutput
 */
class StdoutOutputTest extends TestCase
{
    /**
     * @covers \Jogger\Output\StdoutOutput::__construct
     */
    public function test__construct() {
        $stderr = new StdoutOutput("info");
        $reflector = new ReflectionClass($stderr);
        $level = $reflector->getProperty("level");
        $level->setAccessible(true);
        $this->assertEquals("info", $level->getValue($stderr));
    }
}
