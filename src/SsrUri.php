<?php

namespace ShadowsocksR\Config;

use InvalidArgumentException;
use Shadowsocks\Config\SIP008;

/**
 * ShadowsocksR URI 处理类
 *
 * 负责处理 ShadowsocksR 的 URI 格式和与标准 Shadowsocks 配置的转换
 */
class SsrUri
{
    /**
     * 将多个服务器配置编码为 URI 列表
     *
     * @param ServerConfig[] $configs 服务器配置列表
     * @return string[] URI 列表
     */
    public static function encodeMultiple(array $configs): array
    {
        $uris = [];

        foreach ($configs as $config) {
            if (!($config instanceof ServerConfig)) {
                throw new InvalidArgumentException('必须提供 ServerConfig 类型的配置');
            }

            $uris[] = self::encode($config);
        }

        return $uris;
    }

    /**
     * 将服务器配置转换为 SSR URI
     *
     * @param ServerConfig $config 服务器配置
     * @return string SSR URI
     */
    public static function encode(ServerConfig $config): string
    {
        $clientConfig = $config->toClientConfig();
        return $clientConfig->toSsrUri();
    }

    /**
     * 将多个 URI 解码为服务器配置列表
     *
     * @param string[] $uris URI 列表
     * @return ServerConfig[] 服务器配置列表
     * @throws InvalidArgumentException 如果任何 URI 格式错误
     */
    public static function decodeMultiple(array $uris): array
    {
        $configs = [];

        foreach ($uris as $uri) {
            $configs[] = self::decode($uri);
        }

        return $configs;
    }

    /**
     * 将 SSR URI 转换为服务器配置
     *
     * @param string $uri SSR URI
     * @return ServerConfig 服务器配置
     * @throws InvalidArgumentException 如果 URI 格式错误
     */
    public static function decode(string $uri): ServerConfig
    {
        $clientConfig = ClientConfig::fromSsrUri($uri);

        // 生成随机 UUID 作为服务器 ID
        $id = self::generateUUID();

        // 创建服务器配置
        $serverConfig = new ServerConfig(
            $id,
            $clientConfig->getServer(),
            $clientConfig->getServerPort(),
            $clientConfig->getPassword(),
            $clientConfig->getMethod(),
            $clientConfig->getProtocol(),
            $clientConfig->getObfs()
        );

        // 设置其他参数
        if ($clientConfig->getProtocolParam() !== null) {
            $serverConfig->setProtocolParam($clientConfig->getProtocolParam());
        }

        if ($clientConfig->getObfsParam() !== null) {
            $serverConfig->setObfsParam($clientConfig->getObfsParam());
        }

        if ($clientConfig->getRemarks() !== null) {
            $serverConfig->setRemarks($clientConfig->getRemarks());
        }

        return $serverConfig;
    }

    /**
     * 生成随机 UUID
     *
     * @return string UUID
     */
    private static function generateUUID(): string
    {
        $data = random_bytes(16);

        // 设置版本为 4
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);

        // 设置变种为 RFC 4122
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // 格式化为标准 UUID 字符串
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * 将标准 SIP008 配置中的服务器转换为支持 SSR 功能的服务器配置
     *
     * @param SIP008 $sip008 标准 SIP008 配置
     * @param string|null $defaultProtocol 默认协议
     * @param string|null $defaultObfs 默认混淆
     * @return ServerConfig[] 服务器配置列表
     */
    public static function convertFromSIP008(SIP008 $sip008, ?string $defaultProtocol = null, ?string $defaultObfs = null): array
    {
        $ssrServers = [];

        foreach ($sip008->getServers() as $server) {
            $ssrServer = new ServerConfig(
                $server->getId(),
                $server->getServer(),
                $server->getServerPort(),
                $server->getPassword(),
                $server->getMethod(),
                $defaultProtocol,
                $defaultObfs
            );

            if ($server->getRemarks() !== null) {
                $ssrServer->setRemarks($server->getRemarks());
            }

            $ssrServers[] = $ssrServer;
        }

        return $ssrServers;
    }

    /**
     * 将 SSR 服务器配置转换为兼容标准 SIP008 的服务器配置
     *
     * @param ServerConfig[] $ssrServers SSR 服务器配置列表
     * @return \Shadowsocks\Config\ServerConfig[] 标准服务器配置列表
     */
    public static function convertToStandardServers(array $ssrServers): array
    {
        $standardServers = [];

        foreach ($ssrServers as $ssrServer) {
            if (!($ssrServer instanceof ServerConfig)) {
                throw new InvalidArgumentException('必须提供 ServerConfig 类型的配置');
            }

            $standardServer = new \Shadowsocks\Config\ServerConfig(
                $ssrServer->getId(),
                $ssrServer->getServer(),
                $ssrServer->getServerPort(),
                $ssrServer->getPassword(),
                $ssrServer->getMethod()
            );

            // 将协议和混淆信息作为插件处理（如果有插件支持的话）
            $plugin = null;
            $pluginOpts = null;

            if ($ssrServer->getObfs() !== null && $ssrServer->getObfs() !== 'plain') {
                $plugin = 'obfs-local';
                $obfsType = $ssrServer->getObfs();
                $obfsParam = $ssrServer->getObfsParam();

                if ($obfsType === 'http_simple') {
                    $obfsType = 'http';
                } elseif ($obfsType === 'tls1.2_ticket_auth') {
                    $obfsType = 'tls';
                }

                $pluginOpts = "obfs=$obfsType";

                if (!empty($obfsParam)) {
                    $pluginOpts .= ";obfs-host=$obfsParam";
                }
            }

            if ($plugin !== null) {
                $standardServer->setPlugin($plugin);
                $standardServer->setPluginOpts($pluginOpts);
            }

            if ($ssrServer->getRemarks() !== null) {
                $standardServer->setRemarks($ssrServer->getRemarks());
            }

            $standardServers[] = $standardServer;
        }

        return $standardServers;
    }
} 