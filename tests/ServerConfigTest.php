<?php

namespace ShadowsocksR\Config\Tests;

use ShadowsocksR\Config\Exception\InvalidConfigurationException;
use PHPUnit\Framework\TestCase;
use ShadowsocksR\Config\ClientConfig;
use ShadowsocksR\Config\ServerConfig;

class ServerConfigTest extends TestCase
{
    /**
     * 测试基本构造函数和getter
     */
    public function testConstructorAndGetters()
    {
        $config = new ServerConfig(
            'test-id',
            'example.com',
            8388,
            'password',
            'aes-256-cfb',
            'auth_chain_a',
            'tls1.2_ticket_auth'
        );

        $this->assertEquals('test-id', $config->getId());
        $this->assertEquals('example.com', $config->getServer());
        $this->assertEquals(8388, $config->getServerPort());
        $this->assertEquals('password', $config->getPassword());
        $this->assertEquals('aes-256-cfb', $config->getMethod());
        $this->assertEquals('auth_chain_a', $config->getProtocol());
        $this->assertEquals('tls1.2_ticket_auth', $config->getObfs());
    }

    /**
     * 测试转换为客户端配置
     */
    public function testToClientConfig()
    {
        $serverConfig = new ServerConfig(
            'test-id',
            'example.com',
            8388,
            'password',
            'aes-256-cfb',
            'auth_chain_a',
            'tls1.2_ticket_auth'
        );

        $serverConfig->setProtocolParam('32');
        $serverConfig->setObfsParam('cloudflare.com');
        $serverConfig->setRemarks('Test Server');

        $clientConfig = $serverConfig->toClientConfig(1080);

        $this->assertInstanceOf(ClientConfig::class, $clientConfig);
        $this->assertEquals('example.com', $clientConfig->getServer());
        $this->assertEquals(8388, $clientConfig->getServerPort());
        $this->assertEquals(1080, $clientConfig->getLocalPort());
        $this->assertEquals('password', $clientConfig->getPassword());
        $this->assertEquals('aes-256-cfb', $clientConfig->getMethod());
        $this->assertEquals('auth_chain_a', $clientConfig->getProtocol());
        $this->assertEquals('32', $clientConfig->getProtocolParam());
        $this->assertEquals('tls1.2_ticket_auth', $clientConfig->getObfs());
        $this->assertEquals('cloudflare.com', $clientConfig->getObfsParam());
        $this->assertEquals('Test Server', $clientConfig->getRemarks());
    }

    /**
     * 测试JSON序列化和反序列化
     */
    public function testJsonSerializeAndDeserialize()
    {
        $config = new ServerConfig(
            'test-id',
            'example.com',
            8388,
            'password',
            'aes-256-cfb',
            'auth_chain_a',
            'tls1.2_ticket_auth'
        );

        $config->setProtocolParam('32');
        $config->setObfsParam('cloudflare.com');
        $config->setRemarks('Test Server');

        $json = $config->toJson();

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('test-id', $decoded['id']);
        $this->assertEquals('example.com', $decoded['server']);
        $this->assertEquals(8388, $decoded['server_port']);
        $this->assertEquals('password', $decoded['password']);
        $this->assertEquals('aes-256-cfb', $decoded['method']);
        $this->assertEquals('auth_chain_a', $decoded['protocol']);
        $this->assertEquals('32', $decoded['protocol_param']);
        $this->assertEquals('tls1.2_ticket_auth', $decoded['obfs']);
        $this->assertEquals('cloudflare.com', $decoded['obfs_param']);
        $this->assertEquals('Test Server', $decoded['remarks']);

        // 反序列化测试
        $newConfig = ServerConfig::fromJson($json);
        $this->assertInstanceOf(ServerConfig::class, $newConfig);
        $this->assertEquals('test-id', $newConfig->getId());
        $this->assertEquals('example.com', $newConfig->getServer());
        $this->assertEquals(8388, $newConfig->getServerPort());
        $this->assertEquals('password', $newConfig->getPassword());
        $this->assertEquals('aes-256-cfb', $newConfig->getMethod());
        $this->assertEquals('auth_chain_a', $newConfig->getProtocol());
        $this->assertEquals('32', $newConfig->getProtocolParam());
        $this->assertEquals('tls1.2_ticket_auth', $newConfig->getObfs());
        $this->assertEquals('cloudflare.com', $newConfig->getObfsParam());
        $this->assertEquals('Test Server', $newConfig->getRemarks());
    }

    /**
     * 测试无效的JSON反序列化
     */
    public function testInvalidJsonDeserialization()
    {
        $this->expectException(InvalidConfigurationException::class);
        ServerConfig::fromJson('{invalid json}');
    }

    /**
     * 测试缺少必要字段的JSON反序列化
     */
    public function testMissingFieldsJsonDeserialization()
    {
        $this->expectException(InvalidConfigurationException::class);
        ServerConfig::fromJson('{"server":"example.com"}');
    }
}
