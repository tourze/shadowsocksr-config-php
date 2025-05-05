# ShadowsocksR Config PHP

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/shadowsocksr-config-php.svg?style=flat-square)](https://packagist.org/packages/tourze/shadowsocksr-config-php)
[![Build Status](https://img.shields.io/travis/tourze/shadowsocksr-config-php/master.svg?style=flat-square)](https://travis-ci.org/tourze/shadowsocksr-config-php)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/shadowsocksr-config-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/shadowsocksr-config-php)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/shadowsocksr-config-php.svg?style=flat-square)](https://packagist.org/packages/tourze/shadowsocksr-config-php)

A PHP library for handling ShadowsocksR configurations. This package extends
the [shadowsocks-config-php](https://github.com/tourze/shadowsocks-config-php) with additional support for
ShadowsocksR-specific features like protocol and obfuscation.

## Features

- Complete support for ShadowsocksR-specific protocol and obfuscation settings
- SSR URI encoding and decoding
- Conversion tools between standard Shadowsocks and ShadowsocksR configurations
- Full PSR-4 compliance
- Unit tests with PHPUnit

## Installation

```bash
composer require tourze/shadowsocksr-config-php
```

## Quick Start

```php
<?php

use ShadowsocksR\Config\ServerConfig;
use ShadowsocksR\Config\ClientConfig;
use ShadowsocksR\Config\SsrUri;

// Create a server configuration
$serverConfig = new ServerConfig(
    'server-uuid',
    'example.com',
    8388,
    'password',
    'chacha20-ietf-poly1305',
    'auth_chain_a',
    'tls1.2_ticket_auth'
);

// Generate SSR URI
$ssrUri = SsrUri::encode($serverConfig);
echo $ssrUri; // ssr://...

// Decode SSR URI back to server config
$decodedConfig = SsrUri::decode($ssrUri);
```

## Documentation

### Basic Configuration

#### Server Configuration

```php
use ShadowsocksR\Config\ServerConfig;

// Create a server configuration
$serverConfig = new ServerConfig(
    'server-uuid',
    'example.com',
    8388,
    'password',
    'chacha20-ietf-poly1305',
    'auth_chain_a',
    'tls1.2_ticket_auth'
);

// Set protocol parameter and obfuscation parameter
$serverConfig->setProtocolParam('32');
$serverConfig->setObfsParam('cloudflare.com');

// Set remarks
$serverConfig->setRemarks('Example Server');

// Convert to JSON
$json = $serverConfig->toJson();

// Create server config from JSON
$configFromJson = ServerConfig::fromJson($json);
```

#### Client Configuration

```php
use ShadowsocksR\Config\ClientConfig;

// Create a client configuration
$clientConfig = new ClientConfig(
    'example.com',
    8388,
    1080,
    'password',
    'chacha20-ietf-poly1305'
);

// Set protocol and obfuscation
$clientConfig->setProtocol('auth_chain_a');
$clientConfig->setObfs('tls1.2_ticket_auth');

// Set protocol parameter and obfuscation parameter
$clientConfig->setProtocolParam('32');
$clientConfig->setObfsParam('cloudflare.com');

// Convert to SSR URI
$ssrUri = $clientConfig->toSsrUri();

// Create client config from SSR URI
$configFromUri = ClientConfig::fromSsrUri($ssrUri);
```

### SSR URI Handling

```php
use ShadowsocksR\Config\SsrUri;
use ShadowsocksR\Config\ServerConfig;

// Generate SSR URI from server config
$serverConfig = new ServerConfig(
    'server-uuid',
    'example.com',
    8388,
    'password',
    'chacha20-ietf-poly1305',
    'auth_chain_a',
    'tls1.2_ticket_auth'
);
$serverConfig->setRemarks('Example Server');

$ssrUri = SsrUri::encode($serverConfig);
echo $ssrUri; // ssr://...

// Decode SSR URI back to server config
$decodedConfig = SsrUri::decode($ssrUri);

// Handle multiple configurations
$servers = [
    $serverConfig,
    // ... more server configs
];

$uris = SsrUri::encodeMultiple($servers);
$decodedServers = SsrUri::decodeMultiple($uris);
```

### Integration with Shadowsocks SIP008

SIP008 is part of the Shadowsocks standard for multi-server configuration. This package provides conversion tools
between standard Shadowsocks servers and ShadowsocksR servers.

```php
use ShadowsocksR\Config\SsrUri;
use ShadowsocksR\Config\ServerConfig;
use Shadowsocks\Config\SIP008;

// Create SSR server configurations
$ssrServer1 = new ServerConfig(
    'uuid-1',
    'server1.example.com',
    8388,
    'password1',
    'chacha20-ietf-poly1305',
    'auth_chain_a',
    'tls1.2_ticket_auth'
);
$ssrServer1->setRemarks('Server 1');

$ssrServer2 = new ServerConfig(
    'uuid-2',
    'server2.example.com',
    8389,
    'password2',
    'aes-256-gcm',
    'auth_aes128_md5',
    'http_simple'
);
$ssrServer2->setRemarks('Server 2');

// Convert SSR servers to standard servers
$standardServers = SsrUri::convertToStandardServers([$ssrServer1, $ssrServer2]);

// Create standard SIP008 configuration
$sip008 = new SIP008();
foreach ($standardServers as $server) {
    $sip008->addServer($server);
}

// Output standard SIP008 JSON
$json = $sip008->toJson();
echo $json;

// Convert from standard SIP008 back to SSR configurations
$standardSip008 = SIP008::fromJson($json);
$ssrServers = SsrUri::convertFromSIP008(
    $standardSip008, 
    'auth_chain_a',  // Default protocol
    'tls1.2_ticket_auth'  // Default obfuscation
);
```

## Protocol Configuration

### Supported Protocol Plugins

ShadowsocksR supports the following protocol plugins:

- `origin`: Original Shadowsocks protocol
- `auth_sha1_v4`: SHA1 authentication version 4
- `auth_aes128_md5`: AES128 authentication with MD5
- `auth_aes128_sha1`: AES128 authentication with SHA1
- `auth_chain_a`: Chain A authentication (recommended)
- `auth_chain_b`: Chain B authentication (recommended)

### Supported Obfuscation Plugins

ShadowsocksR supports the following obfuscation plugins:

- `plain`: No obfuscation
- `http_simple`: HTTP simple obfuscation
- `http_post`: HTTP POST obfuscation
- `tls1.2_ticket_auth`: TLS 1.2 ticket authentication obfuscation (recommended)

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit packages/shadowsocksr-config-php/tests
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
