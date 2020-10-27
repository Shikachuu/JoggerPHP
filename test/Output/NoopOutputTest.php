<?php /** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Output;

use Jogger\Output\NoopOutput;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class NoopOutputTest
 * @package Output
 * @covers \Jogger\Output\BaseOutput
 * @covers \Jogger\Output\NoopOutput
 * @uses \Jogger\Utilities::validateLoglevel
 */
class NoopOutputTest extends TestCase
{
    /**
     * @covers \Jogger\Output\BaseOutput::__construct
     * @covers \Jogger\Output\NoopOutput::__construct
     */
    public function test__construct() {
        $object = new NoopOutput("debug");
        $reflector = new ReflectionClass($object);
        $level = $reflector->getProperty("level");
        $level->setAccessible(true);
        $this->assertEquals("debug", $level->getValue($object));
    }
    /**
     * @covers \Jogger\Output\BaseOutput::setLevel
     * @covers \Jogger\Output\NoopOutput::setLevel
     */
    public function testGetLevel() {
        $object = new NoopOutput("info");
        $this->assertEquals("info", $object->getLevel());
    }

    /**
     * @covers \Jogger\Output\BaseOutput::getLevel
     * @covers \Jogger\Output\NoopOutput::getLevel
     */
    public function testSetLevel() {
        $object = new NoopOutput("warning");
        $reflector = new ReflectionClass($object);
        $level = $reflector->getProperty("level");
        $level->setAccessible(true);
        $object->setLevel("error");
        $this->assertEquals("error", $level->getValue($object));
    }
}
