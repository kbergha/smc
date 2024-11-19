<?php
declare(strict_types=1);

namespace hello;

use PHPUnit\Framework\TestCase;
use Smc\hello\HelloWorld;
use TypeError;

final class HelloWorldTest extends TestCase
{
    public function testSaysHelloWorld(): void
    {
        $this->assertSame("Hello World!", HelloWorld::sayHello(null));
    }

    public function testSaysHelloName(): void
    {
        $name = "SMC";
        $this->assertSame("Hello SMC!", HelloWorld::sayHello($name));
    }

    public function testSaysHelloWithEmptyString(): void
    {
        $name = "";
        $this->assertSame("Hello!", HelloWorld::sayHello($name));
    }

    public function testTypeErrorExceptionWrongParameterType(): void
    {
        $this->expectException(TypeError::class);
        $dummy = HelloWorld::sayHello(123);
        unset($dummy);
    }

    public function testTypeErrorExceptionMissingParameter(): void
    {
        $this->expectException(TypeError::class);
        $dummy = HelloWorld::sayHello();
        unset($dummy);
    }
}