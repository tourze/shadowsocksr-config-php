# ShadowsocksR 配置 PHP

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/shadowsocksr-config-php.svg?style=flat-square)](https://packagist.org/packages/tourze/shadowsocksr-config-php)
[![Build Status](https://img.shields.io/travis/tourze/shadowsocksr-config-php/master.svg?style=flat-square)](https://travis-ci.org/tourze/shadowsocksr-config-php)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/shadowsocksr-config-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/shadowsocksr-config-php)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/shadowsocksr-config-php.svg?style=flat-square)](https://packagist.org/packages/tourze/shadowsocksr-config-php)

一个用于处理 ShadowsocksR 配置的 PHP 库。该包扩展了 [shadowsocks-config-php](https://github.com/tourze/shadowsocks-config-php) 的功能，增加了对 ShadowsocksR 特有功能（如协议和混淆）的支持。

## 特性

- 完整支持 ShadowsocksR 特有的协议（protocol）和混淆（obfs）设置
- SSR URI 编码和解码
- 标准 Shadowsocks 和 ShadowsocksR 配置间的转换工具
- 完全符合 PSR-4 规范
- 使用 PHPUnit 进行单元测试

## 安装

```bash
composer require tourze/shadowsocksr-config-php
```

## 快速开始

```php
<?php

use ShadowsocksR\Config\ServerConfig;
use ShadowsocksR\Config\ClientConfig;
use ShadowsocksR\Config\SsrUri;

// 创建服务器配置
$serverConfig = new ServerConfig(
    'server-uuid',
    'example.com',
    8388,
    'password',
    'chacha20-ietf-poly1305',
    'auth_chain_a',
    'tls1.2_ticket_auth'
);

// 生成 SSR URI
$ssrUri = SsrUri::encode($serverConfig);
echo $ssrUri; // ssr://...

// 将 SSR URI 解码回服务器配置
$decodedConfig = SsrUri::decode($ssrUri);
```

## 文档

### 基本配置

#### 服务器配置

```php
use ShadowsocksR\Config\ServerConfig;

// 创建服务器配置
$serverConfig = new ServerConfig(
    'server-uuid',
    'example.com',
    8388,
    'password',
    'chacha20-ietf-poly1305',
    'auth_chain_a',
    'tls1.2_ticket_auth'
);

// 设置协议参数和混淆参数
$serverConfig->setProtocolParam('32');
$serverConfig->setObfsParam('cloudflare.com');

// 设置备注
$serverConfig->setRemarks('示例服务器');

// 转换为 JSON
$json = $serverConfig->toJson();

// 从 JSON 创建服务器配置
$configFromJson = ServerConfig::fromJson($json);
```

#### 客户端配置

```php
use ShadowsocksR\Config\ClientConfig;

// 创建客户端配置
$clientConfig = new ClientConfig(
    'example.com',
    8388,
    1080,
    'password',
    'chacha20-ietf-poly1305'
);

// 设置协议和混淆
$clientConfig->setProtocol('auth_chain_a');
$clientConfig->setObfs('tls1.2_ticket_auth');

// 设置协议参数和混淆参数
$clientConfig->setProtocolParam('32');
$clientConfig->setObfsParam('cloudflare.com');

// 转换为 SSR URI
$ssrUri = $clientConfig->toSsrUri();

// 从 SSR URI 创建客户端配置
$configFromUri = ClientConfig::fromSsrUri($ssrUri);
```

### SSR URI 处理

```php
use ShadowsocksR\Config\SsrUri;
use ShadowsocksR\Config\ServerConfig;

// 从服务器配置生成 SSR URI
$serverConfig = new ServerConfig(
    'server-uuid',
    'example.com',
    8388,
    'password',
    'chacha20-ietf-poly1305',
    'auth_chain_a',
    'tls1.2_ticket_auth'
);
$serverConfig->setRemarks('示例服务器');

$ssrUri = SsrUri::encode($serverConfig);
echo $ssrUri; // ssr://...

// 将 SSR URI 解码回服务器配置
$decodedConfig = SsrUri::decode($ssrUri);

// 处理多个配置
$servers = [
    $serverConfig,
    // ... 更多服务器配置
];

$uris = SsrUri::encodeMultiple($servers);
$decodedServers = SsrUri::decodeMultiple($uris);
```

### 与 Shadowsocks SIP008 集成

SIP008 是 Shadowsocks 标准中用于多服务器配置的部分。本包提供了标准 Shadowsocks 服务器和 ShadowsocksR 服务器之间的转换工具。

```php
use ShadowsocksR\Config\SsrUri;
use ShadowsocksR\Config\ServerConfig;
use Shadowsocks\Config\SIP008;

// 创建 SSR 服务器配置
$ssrServer1 = new ServerConfig(
    'uuid-1',
    'server1.example.com',
    8388,
    'password1',
    'chacha20-ietf-poly1305',
    'auth_chain_a',
    'tls1.2_ticket_auth'
);
$ssrServer1->setRemarks('服务器 1');

$ssrServer2 = new ServerConfig(
    'uuid-2',
    'server2.example.com',
    8389,
    'password2',
    'aes-256-gcm',
    'auth_aes128_md5',
    'http_simple'
);
$ssrServer2->setRemarks('服务器 2');

// 将 SSR 服务器转换为标准服务器
$standardServers = SsrUri::convertToStandardServers([$ssrServer1, $ssrServer2]);

// 创建标准 SIP008 配置
$sip008 = new SIP008();
foreach ($standardServers as $server) {
    $sip008->addServer($server);
}

// 输出标准 SIP008 JSON
$json = $sip008->toJson();
echo $json;

// 从标准 SIP008 转回 SSR 配置
$standardSip008 = SIP008::fromJson($json);
$ssrServers = SsrUri::convertFromSIP008(
    $standardSip008, 
    'auth_chain_a',  // 默认协议
    'tls1.2_ticket_auth'  // 默认混淆
);
```

## 协议配置说明

### 支持的协议插件

ShadowsocksR 支持以下协议插件：

- `origin`: 原始 Shadowsocks 协议
- `auth_sha1_v4`: SHA1 认证 v4 版本
- `auth_aes128_md5`: AES128 认证 MD5 版本
- `auth_aes128_sha1`: AES128 认证 SHA1 版本
- `auth_chain_a`: Chain A 认证（推荐）
- `auth_chain_b`: Chain B 认证（推荐）

### 支持的混淆插件

ShadowsocksR 支持以下混淆插件：

- `plain`: 无混淆
- `http_simple`: HTTP 简单混淆
- `http_post`: HTTP POST 混淆
- `tls1.2_ticket_auth`: TLS 1.2 票据认证混淆（推荐）

## 测试

运行测试套件：

```bash
./vendor/bin/phpunit packages/shadowsocksr-config-php/tests
```

## 贡献

请查看 [CONTRIBUTING.md](CONTRIBUTING.md) 了解详情。

## 许可证

MIT 许可证。请查看 [License File](LICENSE) 获取更多信息。
