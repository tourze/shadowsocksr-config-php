<?php

namespace ShadowsocksR\Config\Tests;

use PHPUnit\Framework\TestCase;
use ShadowsocksR\Config\ClientConfig;

class ClientConfigTest extends TestCase
{
    /**
     * 测试基本构造函数和getter
     */
    public function testConstructorAndGetters()
    {
        $config = new ClientConfig(
            'example.com',
            8388,
            1080,
            'password',
            'aes-256-cfb'
        );

        $this->assertEquals('example.com', $config->getServer());
        $this->assertEquals(8388, $config->getServerPort());
        $this->assertEquals(1080, $config->getLocalPort());
        $this->assertEquals('password', $config->getPassword());
        $this->assertEquals('aes-256-cfb', $config->getMethod());
    }

    /**
     * 测试SSR链接生成
     */
    public function testToSsrUri()
    {
        $config = new ClientConfig(
            'example.com',
            8388,
            1080,
            'password',
            'aes-256-cfb'
        );

        $config->setProtocol('auth_chain_a');
        $config->setObfs('tls1.2_ticket_auth');
        $config->setProtocolParam('32');
        $config->setObfsParam('cloudflare.com');
        $config->setRemarks('Test Client');

        $uri = $config->toSsrUri();

        $this->assertIsString($uri);
        $this->assertStringStartsWith('ssr://', $uri);

        // 解码检查
        $encodedPart = substr($uri, 6); // 去掉 'ssr://' 前缀
        $decodedPart = base64_decode($encodedPart, true);
        $this->assertIsString($decodedPart);

        // 检查解码后的字符串包含正确的信息
        $this->assertStringContainsString('example.com:8388:auth_chain_a:aes-256-cfb:tls1.2_ticket_auth:', $decodedPart);
        $this->assertStringContainsString('obfsparam=' . base64_encode('cloudflare.com'), $decodedPart);
        $this->assertStringContainsString('protoparam=' . base64_encode('32'), $decodedPart);
        $this->assertStringContainsString('remarks=' . base64_encode('Test Client'), $decodedPart);
    }

    /**
     * 测试从SSR链接解析
     */
    public function testFromSsrUri()
    {
        $config = new ClientConfig(
            'example.com',
            8388,
            1080,
            'password',
            'aes-256-cfb'
        );

        $config->setProtocol('auth_chain_a');
        $config->setObfs('tls1.2_ticket_auth');
        $config->setProtocolParam('32');
        $config->setObfsParam('cloudflare.com');
        $config->setRemarks('Test Client');

        $uri = $config->toSsrUri();

        // 从URI解析
        $newConfig = ClientConfig::fromSsrUri($uri);

        $this->assertInstanceOf(ClientConfig::class, $newConfig);
        $this->assertEquals('example.com', $newConfig->getServer());
        $this->assertEquals(8388, $newConfig->getServerPort());
        $this->assertEquals('password', $newConfig->getPassword());
        $this->assertEquals('aes-256-cfb', $newConfig->getMethod());
        $this->assertEquals('auth_chain_a', $newConfig->getProtocol());
        $this->assertEquals('32', $newConfig->getProtocolParam());
        $this->assertEquals('tls1.2_ticket_auth', $newConfig->getObfs());
        $this->assertEquals('cloudflare.com', $newConfig->getObfsParam());
        $this->assertEquals('Test Client', $newConfig->getRemarks());
    }

    /**
     * 测试无效的SSR URI解析
     */
    public function testInvalidSsrUriParse()
    {
        $this->expectException(\InvalidArgumentException::class);
        ClientConfig::fromSsrUri('invalid-uri');
    }

    /**
     * 测试无效的SSR URI格式
     */
    public function testInvalidSsrUriFormat()
    {
        $this->expectException(\InvalidArgumentException::class);
        // 编码一个格式不正确的URI
        $invalidUri = 'ssr://' . base64_encode('invalid:format');
        ClientConfig::fromSsrUri($invalidUri);
    }

    /**
     * 测试JSON序列化和反序列化
     */
    public function testJsonSerializeAndDeserialize()
    {
        $config = new ClientConfig(
            'example.com',
            8388,
            1080,
            'password',
            'aes-256-cfb'
        );

        $config->setProtocol('auth_chain_a');
        $config->setObfs('tls1.2_ticket_auth');
        $config->setProtocolParam('32');
        $config->setObfsParam('cloudflare.com');
        $config->setRemarks('Test Client');

        $json = $config->toJson();
        $this->assertIsString($json);

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('example.com', $decoded['server']);
        $this->assertEquals(8388, $decoded['server_port']);
        $this->assertEquals(1080, $decoded['local_port']);
        $this->assertEquals('password', $decoded['password']);
        $this->assertEquals('aes-256-cfb', $decoded['method']);
        $this->assertEquals('auth_chain_a', $decoded['protocol']);
        $this->assertEquals('32', $decoded['protocol_param']);
        $this->assertEquals('tls1.2_ticket_auth', $decoded['obfs']);
        $this->assertEquals('cloudflare.com', $decoded['obfs_param']);
        $this->assertEquals('Test Client', $decoded['remarks']);

        // 从JSON反序列化
        $newConfig = ClientConfig::fromJson($json);

        $this->assertInstanceOf(ClientConfig::class, $newConfig);
        $this->assertEquals('example.com', $newConfig->getServer());
        $this->assertEquals(8388, $newConfig->getServerPort());
        $this->assertEquals(1080, $newConfig->getLocalPort());
        $this->assertEquals('password', $newConfig->getPassword());
        $this->assertEquals('aes-256-cfb', $newConfig->getMethod());
        $this->assertEquals('auth_chain_a', $newConfig->getProtocol());
        $this->assertEquals('32', $newConfig->getProtocolParam());
        $this->assertEquals('tls1.2_ticket_auth', $newConfig->getObfs());
        $this->assertEquals('cloudflare.com', $newConfig->getObfsParam());
        $this->assertEquals('Test Client', $newConfig->getRemarks());
    }

    /**
     * 测试无效的JSON反序列化
     */
    public function testInvalidJsonDeserialization()
    {
        $this->expectException(\InvalidArgumentException::class);
        ClientConfig::fromJson('{invalid json}');
    }

    /**
     * 测试缺少必要字段的JSON反序列化
     */
    public function testMissingFieldsJsonDeserialization()
    {
        $this->expectException(\InvalidArgumentException::class);
        ClientConfig::fromJson('{"server":"example.com"}');
    }
}
