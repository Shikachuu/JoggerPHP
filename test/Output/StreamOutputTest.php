<?php /** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Output;

use Jogger\Output\StreamOutput;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class StreamOutputTest
 * @covers \Jogger\Output\StreamOutput
 * @covers \Jogger\Output\StderrOutput
 * @covers \Jogger\Output\StdoutOutput
 * @uses \Jogger\Output\BaseOutput
 */
class StreamOutputTest extends TestCase
{
    private vfsStreamDirectory $root;
    private string $fileName = "/test.txt";

    protected function setUp(): void {
        $this->root = vfsStream::setup("root", 0777, [$this->fileName => ""]);
    }

    /**
     * @covers \Jogger\Output\StdoutOutput::close
     * @covers \Jogger\Output\StderrOutput::close
     * @covers \Jogger\Output\StreamOutput::close
     */
    public function testClose() {
        $object = new StreamOutput("debug", $this->root->url() . $this->fileName);
        $this->assertTrue($object->close());
    }

    /**
     * @covers \Jogger\Output\StreamOutput::__construct
     */
    public function test__construct() {
        $object = new StreamOutput("debug", $this->root->url() . $this->fileName);
        $reflector = new ReflectionClass($object);
        $stream = $reflector->getProperty("stream");
        $stream->setAccessible(true);
        $this->assertNotFalse($stream->getValue($object));
    }

    /**
     * @covers \Jogger\Output\StreamOutput::write
     * @covers \Jogger\Output\StderrOutput::write
     * @covers \Jogger\Output\StdoutOutput::write
     */
    public function testWrite() {
        $object = new StreamOutput("debug", $this->root->url() . $this->fileName);
        $object->write("test");
        $content = file_get_contents($this->root->url() . $this->fileName);
        $this->assertNotFalse($content);
        $this->assertEquals("test", $content);
    }

    protected function tearDown(): void {
        if (file_exists($this->root->path() . $this->fileName) === true) {
            rmdir($this->root->path() . $this->fileName);
        }
    }
}
