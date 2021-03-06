<?php /** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Jogger;

use DateTimeImmutable;
use DomainException;
use Exception;
use Jogger\Output\NoopOutput;
use LogicException;
use Output\ArrayOutput;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use stdClass;

/**
 * Class LoggerTest
 * @covers \Jogger\Logger
 * @uses   \Jogger\Output\BaseOutput
 * @uses   \Jogger\Output\NoopOutput
 * @uses   \Jogger\Utilities::validateLoglevel()
 * @uses   \Jogger\Interpolate
 */
class LoggerTest extends TestCase
{
    /**
     * @covers \Jogger\Logger::__construct
     */
    public function test__construct() {
        $logger = new Logger("asd", array(new NoopOutput("info")), "Europe/Budapest");
        $reflector = new ReflectionClass($logger);
        $timeZone = $reflector->getProperty("timeZone");
        $timeZone->setAccessible(true);
        $this->assertEquals(
            "Europe/Budapest",
            $timeZone->getValue($logger)
        );

        $outputs = $reflector->getProperty("outputs");
        $outputs->setAccessible(true);
        $this->assertEquals(
            [new NoopOutput("info")],
            $outputs->getValue($logger)
        );
    }

    /**
     * @covers \Jogger\Logger::addFloat
     * @covers \Jogger\Logger::addBoolean
     * @covers \Jogger\Logger::addInteger
     * @covers \Jogger\Logger::addString
     * @covers \Jogger\Logger::addArray
     * @covers \Jogger\Logger::addException
     * @param string $type
     * @param array $cases
     * @throws ReflectionException
     */
    private function testAbstractDynamicFields(string $type, array $cases) {
        $object = new Logger("test");
        $reflector = new ReflectionClass($object);
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

    /**
     * @covers \Jogger\Logger::setTimeFormatISO8601
     */
    public function testSetTimeFormatISO8601() {
        $object = new Logger("testCaseISO");
        $object->setTimeFormatISO8601();

        $reflector = new ReflectionClass($object);
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

    /**
     * @covers \Jogger\Logger::setTimeFormatUnix
     */
    public function testSetTimeFormatUnix() {
        //1234310400
        $object = new Logger("testCaseISO");
        $object->setTimeFormatUnix();

        $reflector = new ReflectionClass($object);
        $timeFieldFormat = $reflector->getProperty("timeFieldFormat");
        $timeFieldFormat->setAccessible(true);
        $this->assertEquals('U', $timeFieldFormat->getValue($object));
        $this->assertEquals(
            "1234310400",
            date($timeFieldFormat->getValue($object), 1234310400)
        );
    }

    /**
     * @covers \Jogger\Logger::setStaticFields
     */
    public function testSetStaticFields() {
        $object = new Logger("test");
        $object->setStaticFields(["test" => "pass"]);

        $reflector = new ReflectionClass($object);
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

    /**
     * @covers \Jogger\Logger::addFloat
     */
    public function testAddFloat() {
        $cases = [
            "case1" => 53.2,
            "case2" => 53.276234,
            "case3" => 12312.2
        ];
        $this->testAbstractDynamicFields("addFloat", $cases);
    }

    /**
     * @covers \Jogger\Logger::addString
     */
    public function testAddString() {
        $cases = [
            "case1" => "hello",
            "case2" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer sit amet metus nec erat pulvinar pellentesque eget ac lectus. Ut a augue sagittis, ornare lectus non, hendrerit sem. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Sed eget auctor enim. In hac habitasse platea dictumst. Aenean dui ante, feugiat sed elit sit amet, bibendum accumsan tortor. Nullam a metus justo. Aenean gravida mauris sed dignissim venenatis. Proin at risus aliquet, consequat massa non, rutrum nunc. Fusce feugiat vestibulum mattis. Etiam ante mi, interdum vitae blandit eu, congue in elit. Donec posuere nec est eget pellentesque. Aenean ultrices vulputate nulla ornare varius. Aliquam a orci mauris. ",
            "case3" => "34561"
        ];
        $this->testAbstractDynamicFields("addString", $cases);
    }

    /**
     * @covers \Jogger\Logger::addInteger
     */
    public function testAddInteger() {
        $cases = [
            "case1" => 53,
            "case2" => 56,
            "case3" => 12312
        ];
        $this->testAbstractDynamicFields("addInteger", $cases);
    }

    /**
     * @covers \Jogger\Logger::addBoolean
     */
    public function testAddBoolean() {
        $cases = [
            "case1" => true,
            "case2" => false,
        ];
        $this->testAbstractDynamicFields("addBoolean", $cases);
    }

    /**
     * @covers \Jogger\Logger::addBoolean
     */
    public function testAddArray() {
        $cases = [
            "case1" => [5, 4, 3, 2, 1],
            "case2" => ["asd" => "dsa", "761" => 2],
            "case3" => [],
            "case4" => ["aasd", "das"],
            "case5" => [new stdClass(), new stdClass()]
        ];
        $this->testAbstractDynamicFields("addArray", $cases);
    }

    /**
     * @covers \Jogger\Logger::addException
     */
    public function testAddException() {
        $cases = [
            "case1" => new InvalidArgumentException("test"),
            "case2" => new Exception("test"),
            "case3" => new LogicException("test"),
            "case4" => new DomainException("test"),
            "case5" => new RuntimeException("test")
        ];
        $object = new Logger("test");
        $reflector = new ReflectionClass($object);
        $dynamicFields = $reflector->getProperty("dynamicFields");
        $dynamicFields->setAccessible(true);
        foreach ($cases as $key => $value) {
            $returnValue = $object->addException($key, $value);
            $this->assertObjectHasAttribute(
                $key,
                $dynamicFields->getValue($returnValue)
            );
            $this->assertEquals(
                $object,
                $returnValue
            );
            $this->assertObjectHasAttribute("exception", $dynamicFields->getValue($object)->$key);
            $this->assertObjectHasAttribute("code", $dynamicFields->getValue($object)->$key);
            $this->assertObjectHasAttribute("message", $dynamicFields->getValue($object)->$key);
            $this->assertObjectHasAttribute("file", $dynamicFields->getValue($object)->$key);
            $this->assertObjectHasAttribute("line", $dynamicFields->getValue($object)->$key);
            $this->assertObjectHasAttribute("trace", $dynamicFields->getValue($object)->$key);
        }
    }

    /**
     * @covers \Jogger\Logger::logLevelToNumber
     */
    public function testLogLevelToNumberPositiveCases() {
        $positiveCases = [
            "testAlert" => "alert",
            "testDebug" => "debug",
            "testCritical" => "critical",
            "testEmergency" => "emergency",
            "testError" => "error",
            "testNotice" => "notice",
            "testWarning" => "warning",
            "testInfo" => "info"
        ];
        $negativeCases = [
            "testString" => "apple",
            "testInt" => 200,
        ];
        $object = new Logger("test");
        $reflector = new ReflectionClass($object);
        $logLevelToNumber = $reflector->getMethod("logLevelToNumber");
        $logLevelToNumber->setAccessible(true);
        foreach ($positiveCases as $key => $case) {
            $this->assertIsInt($logLevelToNumber->invokeArgs($object, [$case]));
        }
        foreach ($negativeCases as $key => $case) {
            $this->expectException("InvalidArgumentException");
            $logLevelToNumber->invokeArgs($object, [$case]);
        }
    }

    /**
     * @covers \Jogger\Logger::createLogLine
     */
    public function testCreateLogLine() {
        /*logger, level, $message, $expect*/
        $case4Logger = new Logger("test");
        $case4Logger->addString("test", "dynamicFieldReset")->log("info", "");
        $cases = [
            "case1" => [
                new Logger("test"),
                "debug",
                "hello",
                json_encode(
                    [
                        "timestamp" => (new DateTimeImmutable())->format("U"),
                        "level" => "debug",
                        "message" => "hello"
                    ],
                    JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK
                )
            ],
            "case2" => [
                (new Logger("test"))->addFloat("test", 12.5),
                "debug",
                "hello",
                json_encode(
                    [
                        "timestamp" => (new DateTimeImmutable())->format("U"),
                        "level" => "debug",
                        "message" => "hello",
                        "test" => 12.5
                    ],
                    JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK
                )
            ],
            "case3" => [
                (new Logger("test"))->addFloat("test", 12.5)->addBoolean("test", true),
                "debug",
                "hello",
                json_encode(
                    [
                        "timestamp" => (new DateTimeImmutable())->format("U"),
                        "level" => "debug",
                        "message" => "hello",
                        "test" => true
                    ],
                    JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK
                )
            ],
            "case4" => [
                $case4Logger->addBoolean("valid", true),
                "debug",
                "hello",
                json_encode(
                    [
                        "timestamp" => (int)(new DateTimeImmutable())->format("U"),
                        "level" => "debug",
                        "message" => "hello",
                        "valid" => true
                    ]
                )
            ]
        ];
        foreach ($cases as $key => $case) {
            $reflector = new ReflectionClass($case[0]);
            $createLogLine = $reflector->getMethod("createLogLine");
            $createLogLine->setAccessible(true);
            $this->assertEquals(
                $case[3],
                $createLogLine->invokeArgs($case[0], [$case[1], $case[2]])
            );
        }
    }

    /**
     * @covers \Jogger\Logger::log
     * @uses   \Output\ArrayOutput
     * @uses   \Jogger\Interpolate
     * @uses   \Jogger\Utilities
     */
    public function testLog() {
        require_once "Output/ArrayOutput.php";
        $debugOutput = new ArrayOutput("debug");
        $infoOutput = new ArrayOutput("info");
        $criticalOutput = new ArrayOutput("critical");
        $logger = new Logger("testLog", [$debugOutput, $infoOutput, $criticalOutput]);
        $logger->log("error", "testMessage");
        $this->assertEquals(1, $debugOutput->getNumberOfMessages());
        $this->assertEquals(1, $infoOutput->getNumberOfMessages());
        $this->assertEquals(0, $criticalOutput->getNumberOfMessages());
        $logger->log("alert", "testMessage");
        $this->assertEquals(2, $debugOutput->getNumberOfMessages());
        $this->assertEquals(2, $infoOutput->getNumberOfMessages());
        $this->assertEquals(1, $criticalOutput->getNumberOfMessages());
        $this->expectException(InvalidArgumentException::class);
        $logger->log("test", "testMessage");
    }
}
