<?php

namespace ShadowsocksR\Config\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ShadowsocksR\Config\BaseConfig;

class BaseConfigTest extends TestCase
{
    /**
     * 测试协议getter和setter
     */
    public function testProtocolGetterAndSetter()
    {
        $config = $this->getMockForAbstractClass(BaseConfig::class, ['localhost', 8388, 'password', 'aes-256-cfb']);

        $this->assertNull($config->getProtocol());

        $config->setProtocol('auth_chain_a');
        $this->assertEquals('auth_chain_a', $config->getProtocol());

        $config->setProtocol(null);
        $this->assertNull($config->getProtocol());
    }

    /**
     * 测试协议参数getter和setter
     */
    public function testProtocolParamGetterAndSetter()
    {
        $config = $this->getMockForAbstractClass(BaseConfig::class, ['localhost', 8388, 'password', 'aes-256-cfb']);

        $this->assertNull($config->getProtocolParam());

        $config->setProtocolParam('param');
        $this->assertEquals('param', $config->getProtocolParam());

        $config->setProtocolParam(null);
        $this->assertNull($config->getProtocolParam());
    }

    /**
     * 测试混淆getter和setter
     */
    public function testObfsGetterAndSetter()
    {
        $config = $this->getMockForAbstractClass(BaseConfig::class, ['localhost', 8388, 'password', 'aes-256-cfb']);

        $this->assertNull($config->getObfs());

        $config->setObfs('tls1.2_ticket_auth');
        $this->assertEquals('tls1.2_ticket_auth', $config->getObfs());

        $config->setObfs(null);
        $this->assertNull($config->getObfs());
    }

    /**
     * 测试混淆参数getter和setter
     */
    public function testObfsParamGetterAndSetter()
    {
        $config = $this->getMockForAbstractClass(BaseConfig::class, ['localhost', 8388, 'password', 'aes-256-cfb']);

        $this->assertNull($config->getObfsParam());

        $config->setObfsParam('cloudflare.com');
        $this->assertEquals('cloudflare.com', $config->getObfsParam());

        $config->setObfsParam(null);
        $this->assertNull($config->getObfsParam());
    }

    /**
     * 测试JSON数组生成
     */
    public function testGetBaseJsonArray()
    {
        $config = $this->getMockForAbstractClass(BaseConfig::class, ['localhost', 8388, 'password', 'aes-256-cfb']);
        $config->setProtocol('auth_chain_a');
        $config->setProtocolParam('32');
        $config->setObfs('tls1.2_ticket_auth');
        $config->setObfsParam('cloudflare.com');

        // 使用反射来访问protected方法
        $reflection = new ReflectionClass($config);
        $method = $reflection->getMethod('getBaseJsonArray');
        $method->setAccessible(true);
        $result = $method->invoke($config);

        $this->assertIsArray($result);
        $this->assertEquals('localhost', $result['server']);
        $this->assertEquals(8388, $result['server_port']);
        $this->assertEquals('password', $result['password']);
        $this->assertEquals('aes-256-cfb', $result['method']);
        $this->assertEquals('auth_chain_a', $result['protocol']);
        $this->assertEquals('32', $result['protocol_param']);
        $this->assertEquals('tls1.2_ticket_auth', $result['obfs']);
        $this->assertEquals('cloudflare.com', $result['obfs_param']);
    }
}
