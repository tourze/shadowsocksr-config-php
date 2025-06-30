<?php

namespace ShadowsocksR\Config\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use ShadowsocksR\Config\Exception\InvalidConfigurationException;

class InvalidConfigurationExceptionTest extends TestCase
{
    public function testExceptionCanBeCreated()
    {
        $exception = new InvalidConfigurationException('Test message');
        
        $this->assertInstanceOf(InvalidConfigurationException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }
    
    public function testExceptionExtendsInvalidArgumentException()
    {
        $exception = new InvalidConfigurationException('Test message');
        
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }
}