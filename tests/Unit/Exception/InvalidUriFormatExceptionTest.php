<?php

namespace ShadowsocksR\Config\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use ShadowsocksR\Config\Exception\InvalidUriFormatException;

class InvalidUriFormatExceptionTest extends TestCase
{
    public function testExceptionCanBeCreated()
    {
        $exception = new InvalidUriFormatException('Test message');
        
        $this->assertInstanceOf(InvalidUriFormatException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }
    
    public function testExceptionExtendsInvalidArgumentException()
    {
        $exception = new InvalidUriFormatException('Test message');
        
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }
}