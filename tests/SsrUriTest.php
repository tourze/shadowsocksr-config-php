<?php

namespace ShadowsocksR\Config\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shadowsocks\Config\SIP008;
use ShadowsocksR\Config\ServerConfig;
use ShadowsocksR\Config\SsrUri;

class SsrUriTest extends TestCase
{
    /**
     * 测试 SSR URI 编码和解码
     */
    public function testEncodeAndDecode()
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

        $uri = SsrUri::encode($serverConfig);
        $this->assertIsString($uri);
        $this->assertStringStartsWith('ssr://', $uri);

        $decodedConfig = SsrUri::decode($uri);
        $this->assertInstanceOf(ServerConfig::class, $decodedConfig);

        // 注意：UUID 是随机生成的，不会匹配原来的 ID
        $this->assertEquals('example.com', $decodedConfig->getServer());
        $this->assertEquals(8388, $decodedConfig->getServerPort());
        $this->assertEquals('password', $decodedConfig->getPassword());
        $this->assertEquals('aes-256-cfb', $decodedConfig->getMethod());
        $this->assertEquals('auth_chain_a', $decodedConfig->getProtocol());
        $this->assertEquals('32', $decodedConfig->getProtocolParam());
        $this->assertEquals('tls1.2_ticket_auth', $decodedConfig->getObfs());
        $this->assertEquals('cloudflare.com', $decodedConfig->getObfsParam());
        $this->assertEquals('Test Server', $decodedConfig->getRemarks());
    }

    /**
     * 测试多个配置的编码和解码
     */
    public function testEncodeAndDecodeMultiple()
    {
        $config1 = new ServerConfig(
            'id-1',
            'server1.example.com',
            8388,
            'password1',
            'aes-256-cfb',
            'auth_chain_a',
            'tls1.2_ticket_auth'
        );

        $config2 = new ServerConfig(
            'id-2',
            'server2.example.com',
            8389,
            'password2',
            'chacha20-ietf-poly1305',
            'auth_aes128_md5',
            'http_simple'
        );

        $uris = SsrUri::encodeMultiple([$config1, $config2]);
        $this->assertIsArray($uris);
        $this->assertCount(2, $uris);
        $this->assertStringStartsWith('ssr://', $uris[0]);
        $this->assertStringStartsWith('ssr://', $uris[1]);

        $configs = SsrUri::decodeMultiple($uris);
        $this->assertIsArray($configs);
        $this->assertCount(2, $configs);
        $this->assertInstanceOf(ServerConfig::class, $configs[0]);
        $this->assertInstanceOf(ServerConfig::class, $configs[1]);

        // 检查第一个配置
        $this->assertEquals('server1.example.com', $configs[0]->getServer());
        $this->assertEquals(8388, $configs[0]->getServerPort());
        $this->assertEquals('password1', $configs[0]->getPassword());
        $this->assertEquals('aes-256-cfb', $configs[0]->getMethod());
        $this->assertEquals('auth_chain_a', $configs[0]->getProtocol());
        $this->assertEquals('tls1.2_ticket_auth', $configs[0]->getObfs());

        // 检查第二个配置
        $this->assertEquals('server2.example.com', $configs[1]->getServer());
        $this->assertEquals(8389, $configs[1]->getServerPort());
        $this->assertEquals('password2', $configs[1]->getPassword());
        $this->assertEquals('chacha20-ietf-poly1305', $configs[1]->getMethod());
        $this->assertEquals('auth_aes128_md5', $configs[1]->getProtocol());
        $this->assertEquals('http_simple', $configs[1]->getObfs());
    }

    /**
     * 测试 SIP008 转换
     */
    public function testConvertFromSIP008()
    {
        // 创建标准的 SIP008 配置
        $sip008 = new SIP008();

        $server1 = new \Shadowsocks\Config\ServerConfig(
            'id-1',
            'server1.example.com',
            8388,
            'password1',
            'aes-256-cfb'
        );
        $server1->setRemarks('Server 1');

        $server2 = new \Shadowsocks\Config\ServerConfig(
            'id-2',
            'server2.example.com',
            8389,
            'password2',
            'chacha20-ietf-poly1305'
        );

        $sip008->addServer($server1);
        $sip008->addServer($server2);

        // 转换为 SSR 服务器
        $ssrServers = SsrUri::convertFromSIP008(
            $sip008,
            'auth_chain_a',
            'tls1.2_ticket_auth'
        );

        $this->assertIsArray($ssrServers);
        $this->assertCount(2, $ssrServers);
        $this->assertInstanceOf(ServerConfig::class, $ssrServers[0]);
        $this->assertInstanceOf(ServerConfig::class, $ssrServers[1]);

        // 检查第一个 SSR 服务器
        $this->assertEquals('id-1', $ssrServers[0]->getId());
        $this->assertEquals('server1.example.com', $ssrServers[0]->getServer());
        $this->assertEquals(8388, $ssrServers[0]->getServerPort());
        $this->assertEquals('password1', $ssrServers[0]->getPassword());
        $this->assertEquals('aes-256-cfb', $ssrServers[0]->getMethod());
        $this->assertEquals('auth_chain_a', $ssrServers[0]->getProtocol());
        $this->assertEquals('tls1.2_ticket_auth', $ssrServers[0]->getObfs());
        $this->assertEquals('Server 1', $ssrServers[0]->getRemarks());

        // 检查第二个 SSR 服务器
        $this->assertEquals('id-2', $ssrServers[1]->getId());
        $this->assertEquals('server2.example.com', $ssrServers[1]->getServer());
        $this->assertEquals(8389, $ssrServers[1]->getServerPort());
        $this->assertEquals('password2', $ssrServers[1]->getPassword());
        $this->assertEquals('chacha20-ietf-poly1305', $ssrServers[1]->getMethod());
        $this->assertEquals('auth_chain_a', $ssrServers[1]->getProtocol());
        $this->assertEquals('tls1.2_ticket_auth', $ssrServers[1]->getObfs());
    }

    /**
     * 测试转换为标准服务器
     */
    public function testConvertToStandardServers()
    {
        $ssrServer1 = new ServerConfig(
            'id-1',
            'server1.example.com',
            8388,
            'password1',
            'aes-256-cfb',
            'auth_chain_a',
            'plain'
        );
        $ssrServer1->setRemarks('Server 1');

        $ssrServer2 = new ServerConfig(
            'id-2',
            'server2.example.com',
            8389,
            'password2',
            'chacha20-ietf-poly1305',
            'origin',
            'http_simple'
        );
        $ssrServer2->setObfsParam('example.com');

        // 转换为标准服务器
        $standardServers = SsrUri::convertToStandardServers([$ssrServer1, $ssrServer2]);

        $this->assertIsArray($standardServers);
        $this->assertCount(2, $standardServers);
        $this->assertInstanceOf(\Shadowsocks\Config\ServerConfig::class, $standardServers[0]);
        $this->assertInstanceOf(\Shadowsocks\Config\ServerConfig::class, $standardServers[1]);

        // 检查第一个标准服务器
        $this->assertEquals('id-1', $standardServers[0]->getId());
        $this->assertEquals('server1.example.com', $standardServers[0]->getServer());
        $this->assertEquals(8388, $standardServers[0]->getServerPort());
        $this->assertEquals('password1', $standardServers[0]->getPassword());
        $this->assertEquals('aes-256-cfb', $standardServers[0]->getMethod());
        $this->assertEquals('Server 1', $standardServers[0]->getRemarks());

        // 检查第二个标准服务器
        $this->assertEquals('id-2', $standardServers[1]->getId());
        $this->assertEquals('server2.example.com', $standardServers[1]->getServer());
        $this->assertEquals(8389, $standardServers[1]->getServerPort());
        $this->assertEquals('password2', $standardServers[1]->getPassword());
        $this->assertEquals('chacha20-ietf-poly1305', $standardServers[1]->getMethod());
        $this->assertEquals('obfs-local', $standardServers[1]->getPlugin());
        $this->assertStringContainsString('obfs=http', $standardServers[1]->getPluginOpts());
        $this->assertStringContainsString('obfs-host=example.com', $standardServers[1]->getPluginOpts());
    }

    /**
     * 测试无效类型转换异常
     */
    public function testInvalidTypeException()
    {
        $this->expectException(InvalidArgumentException::class);
        SsrUri::convertToStandardServers(['not-a-server-config']);
    }

    /**
     * 测试 generateUUID 方法
     */
    public function testGenerateUUID()
    {
        // 通过反射访问私有方法
        $reflection = new ReflectionClass(SsrUri::class);
        $method = $reflection->getMethod('generateUUID');
        $method->setAccessible(true);

        $uuid = $method->invoke(null);

        $this->assertIsString($uuid);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);
    }
}
