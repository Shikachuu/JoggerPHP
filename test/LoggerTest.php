<?php
/** @noinspection ALL */
declare(strict_types=1);

namespace Jogger;

use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;

class LoggerTest extends TestCase
{
    private function testAbstractDynamicFields(string $type, array $cases) {
        $object = new Logger("test");
        $reflector = new \ReflectionClass($object);
        $dynamicFields = $reflector->getProperty("dynamicFields");
        $dynamicFields->setAccessible(true);
        foreach ($cases as $key => $value) {
            $newObject = $object->$type($key, $value);
            $this->assertObjectHasAttribute(
                $key,
                $dynamicFields->getValue($newObject)
            );
            $this->assertEquals(
                $object,
                $newObject
            );
            $this->assertEquals(
                $value,
                $dynamicFields->getValue($newObject)->$key
            );
        }
    }

    public function testSetTimeFormatISO8601() {
        $object = new Logger("testCaseISO");
        $object->setTimeFormatISO8601();

        $reflector = new \ReflectionClass($object);
        $timeFieldFormat = $reflector->getProperty("timeFieldFormat");
        $timeFieldFormat->setAccessible(true);
        $this->assertEquals(
            'Y-m-d\TH:i:s.uP',
            $timeFieldFormat->getValue($object)
        );
        $this->assertEquals(
            "2009-02-11T00:00:00.000000+00:00",
            date($timeFieldFormat->getValue($object), 1234310400)
        );
    }

    public function testSetTimeFormatUnix() {
        //1234310400
        $object = new Logger("testCaseISO");
        $object->setTimeFormatUnix();

        $reflector = new \ReflectionClass($object);
        $timeFieldFormat = $reflector->getProperty("timeFieldFormat");
        $timeFieldFormat->setAccessible(true);
        $this->assertEquals('U', $timeFieldFormat->getValue($object));
        $this->assertEquals(
            "1234310400",
            date($timeFieldFormat->getValue($object), 1234310400)
        );
    }

    public function testSetStaticFields() {
        $dummyField = new \stdClass();
        $dummyField->test = "pass";

        $object = new Logger("test");
        $object->setStaticFields($dummyField);

        $reflector = new \ReflectionClass($object);
        $staticFields = $reflector->getProperty("staticFields");
        $staticFields->setAccessible(true);
        $this->assertObjectHasAttribute(
            "test",
            $staticFields->getValue($object)
        );
        $this->assertEquals(
            "pass",
            $staticFields->getValue($object)->test
        );
    }

    public function testAddFloat() {
        $cases = [
            "case1" => 53.2,
            "case2" => 53.276234,
            "case3" => 12312.2
        ];
        $this->testAbstractDynamicFields("addFloat", $cases);
    }

    public function testAddString() {
        $cases = [
            "case1" => "hello",
            "case2" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer sit amet metus nec erat pulvinar pellentesque eget ac lectus. Ut a augue sagittis, ornare lectus non, hendrerit sem. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Sed eget auctor enim. In hac habitasse platea dictumst. Aenean dui ante, feugiat sed elit sit amet, bibendum accumsan tortor. Nullam a metus justo. Aenean gravida mauris sed dignissim venenatis. Proin at risus aliquet, consequat massa non, rutrum nunc. Fusce feugiat vestibulum mattis. Etiam ante mi, interdum vitae blandit eu, congue in elit. Donec posuere nec est eget pellentesque. Aenean ultrices vulputate nulla ornare varius. Aliquam a orci mauris. ",
            "case3" => "34561"
        ];
        $this->testAbstractDynamicFields("addString", $cases);
    }

    public function testAddInteger() {
        $cases = [
            "case1" => 53,
            "case2" => 56,
            "case3" => 12312
        ];
        $this->testAbstractDynamicFields("addInteger", $cases);
    }

    public function testAddBoolean() {
        $cases = [
            "case1" => true,
            "case2" => false,
        ];
        $this->testAbstractDynamicFields("addBoolean", $cases);
    }

    public function testAddArray() {
        $cases = [
            "case1" => [5, 4, 3, 2, 1],
            "case2" => ["asd" => "dsa", "761" => 2],
            "case3" => [],
            "case4" => ["aasd", "das"],
            "case5" => [new \stdClass(), new \stdClass()]
        ];
        $this->testAbstractDynamicFields("addArray", $cases);
    }

    public function testAddException() {
        $cases = [
            "case1" => new InvalidArgumentException("test"),
            "case2" => new \Exception("test"),
            "case3" => new \LogicException("test"),
            "case4" => new \DomainException("test"),
            "case5" => new \RuntimeException("test")
        ];
        $this->testAbstractDynamicFields("addException", $cases);
    }

    public function test__construct() {

    }

    public function testLog() {

    }
}
