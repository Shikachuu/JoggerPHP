<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Jogger;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

class InterpolateTest extends TestCase
{

    public function testSetDefaultContext() {
        $object = new Interpolate();
        $object->setDefaultContext(["test" => "setter"]);

        $reflector = new ReflectionClass($object);
        $defaultContext = $reflector->getProperty("defaultContext");
        $defaultContext->setAccessible(true);
        $this->assertArrayHasKey("test", $defaultContext->getValue($object));

        $object->setDefaultContext(["test2" => "setter", "test3" => "multikey"]);
        $this->assertArrayHasKey("test2", $defaultContext->getValue($object));
        $this->assertArrayHasKey("test3", $defaultContext->getValue($object));
        $this->assertArrayNotHasKey("test", $defaultContext->getValue($object));
    }

    public function testGetDefaultContext() {
        $object = new Interpolate(["test" => "getter"]);

        $reflector = new ReflectionClass($object);
        $defaultContext = $reflector->getProperty("defaultContext");
        $defaultContext->setAccessible(true);
        $this->assertEquals($object->getDefaultContext(), $defaultContext->getValue($object));
    }

    public function testCallWithDefaultContextOnly() {
        $object = new Interpolate(["test" => "pass"]);

        $interpolatedString = $object("this test is {test}");
        $this->assertStringNotContainsString("{test}", $interpolatedString);
        $this->assertStringContainsString("pass", $interpolatedString);

        $object->setDefaultContext(["key1" => "pass1", "key2" => "pass2"]);
        $interpolatedString = $object("this is {key1}, {key2}, {key3}");
        $this->assertStringContainsString("pass1", $interpolatedString);
        $this->assertStringContainsString("pass2", $interpolatedString);
        $this->assertStringNotContainsString("{key1}", $interpolatedString);
        $this->assertStringNotContainsString("{key2}", $interpolatedString);
        $this->assertStringContainsString("{key3}", $interpolatedString);
    }

    public function testCallWithDynamicContextOnly() {
        $object = new Interpolate();

        $interpolatedString = $object("this test is {test}", ["test" => "pass"]);
        $this->assertStringNotContainsString("{test}", $interpolatedString);
        $this->assertStringContainsString("pass", $interpolatedString);

        $interpolatedString = $object("this is {key1}, {key2}, {key3}", ["key1" => "pass1", "key2" => "pass2"]);
        $this->assertStringContainsString("pass1", $interpolatedString);
        $this->assertStringContainsString("pass2", $interpolatedString);
        $this->assertStringNotContainsString("{key1}", $interpolatedString);
        $this->assertStringNotContainsString("{key2}", $interpolatedString);
        $this->assertStringContainsString("{key3}", $interpolatedString);
    }

    public function test__constructEmptyValue() {
        $object = new Interpolate();
        $reflector = new ReflectionClass($object);
        $defaultContext = $reflector->getProperty("defaultContext");
        $defaultContext->setAccessible(true);
        $this->assertEquals([], $defaultContext->getValue($object));
    }

    public function test__constructValue() {
        $object = new Interpolate(["testcase" => "construct value"]);
        $reflector = new ReflectionClass($object);
        $defaultContext = $reflector->getProperty("defaultContext");
        $defaultContext->setAccessible(true);
        $value = $defaultContext->getValue($object);
        $this->assertArrayHasKey("testcase", $value);
    }
}
