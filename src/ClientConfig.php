<?php

namespace ShadowsocksR\Config;

use InvalidArgumentException;

/**
 * ShadowsocksR客户端配置类
 */
class ClientConfig extends BaseConfig
{
    /**
     * 本地监听端口
     */
    private readonly int $localPort;

    /**
     * 创建客户端配置
     *
     * @param string $server 服务器地址
     * @param int $serverPort 服务器端口
     * @param int $localPort 本地端口
     * @param string $password 密码
     * @param string $method 加密方法
     */
    public function __construct(
        string $server,
        int    $serverPort,
        int    $localPort,
        string $password = '',
        string $method = 'chacha20-ietf-poly1305'
    )
    {
        parent::__construct($server, $serverPort, $password, $method);
        $this->localPort = $localPort;
    }

    /**
     * 从SSR链接解析配置
     *
     * @param string $ssrUri SSR链接
     * @return self
     * @throws InvalidArgumentException 如果URI格式错误
     */
    public static function fromSsrUri(string $ssrUri): self
    {
        if (!preg_match('/^ssr:\/\/(.+)$/', $ssrUri, $matches)) {
            throw new InvalidArgumentException('SSR URI格式错误');
        }

        $decoded = base64_decode($matches[1], true);
        if ($decoded === false) {
            throw new InvalidArgumentException('SSR URI base64解码失败');
        }

        // 解析URI部分和参数部分
        $parts = explode('/?', $decoded, 2);
        $mainPart = $parts[0];
        $paramPart = isset($parts[1]) ? $parts[1] : '';

        // 解析主要部分
        $mainSegments = explode(':', $mainPart);
        if (count($mainSegments) < 6) {
            throw new InvalidArgumentException('SSR URI 格式错误: 缺少必要部分');
        }

        $server = $mainSegments[0];
        $serverPort = (int)$mainSegments[1];
        $protocol = $mainSegments[2];
        $method = $mainSegments[3];
        $obfs = $mainSegments[4];
        $userInfo = base64_decode($mainSegments[5], true);

        if ($userInfo === false) {
            throw new InvalidArgumentException('SSR URI user-info base64解码失败');
        }

        // 解析用户信息
        $userSegments = explode(':', $userInfo);
        if (count($userSegments) < 2) {
            throw new InvalidArgumentException('SSR URI 用户信息格式错误');
        }

        $password = $userSegments[1];

        // 创建配置
        $config = new self($server, $serverPort, 1080, $password, $method);
        $config->setProtocol($protocol);
        $config->setObfs($obfs);

        // 解析可选参数
        if (!empty($paramPart)) {
            $params = [];
            parse_str($paramPart, $params);

            if (isset($params['obfsparam'])) {
                $decoded = base64_decode($params['obfsparam'], true);
                $config->setObfsParam($decoded !== false ? $decoded : '');
            }

            if (isset($params['protoparam'])) {
                $decoded = base64_decode($params['protoparam'], true);
                $config->setProtocolParam($decoded !== false ? $decoded : '');
            }

            if (isset($params['remarks'])) {
                $decoded = base64_decode($params['remarks'], true);
                $config->setRemarks($decoded !== false ? $decoded : '');
            }
        }

        return $config;
    }

    /**
     * 从JSON字符串创建ClientConfig
     *
     * @param string $json JSON字符串
     * @return self
     * @throws InvalidArgumentException 如果JSON格式错误或缺少必要字段
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('JSON格式错误: ' . json_last_error_msg());
        }

        // 检查必要字段
        $requiredFields = ['server', 'server_port', 'local_port', 'password', 'method'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("缺少必要字段: {$field}");
            }
        }

        $clientConfig = new self(
            $data['server'],
            (int)$data['server_port'],
            (int)$data['local_port'],
            $data['password'],
            $data['method']
        );

        if (isset($data['protocol'])) {
            $clientConfig->setProtocol($data['protocol']);
        }

        if (isset($data['protocol_param'])) {
            $clientConfig->setProtocolParam($data['protocol_param']);
        }

        if (isset($data['obfs'])) {
            $clientConfig->setObfs($data['obfs']);
        }

        if (isset($data['obfs_param'])) {
            $clientConfig->setObfsParam($data['obfs_param']);
        }

        if (isset($data['remarks'])) {
            $clientConfig->setRemarks($data['remarks']);
        }

        return $clientConfig;
    }

    /**
     * 获取本地端口
     *
     * @return int
     */
    public function getLocalPort(): int
    {
        return $this->localPort;
    }

    /**
     * 生成SSR链接（类似于SS链接，但包含SSR特有参数）
     *
     * @return string
     */
    public function toSsrUri(): string
    {
        $user = base64_encode("{$this->method}:{$this->password}");
        $params = [];

        // 添加基本参数
        $obfsParamValue = $this->obfsParam ?? '';
        $protoParamValue = $this->protocolParam ?? '';
        $remarksValue = $this->remarks ?? '';

        $params[] = "obfsparam=" . base64_encode($obfsParamValue);
        $params[] = "protoparam=" . base64_encode($protoParamValue);
        $params[] = "remarks=" . base64_encode($remarksValue);

        $paramsStr = implode("&", $params);

        // 组装SSR URI
        $protocolValue = $this->protocol ?? 'origin';
        $obfsValue = $this->obfs ?? 'plain';
        $uri = "{$this->server}:{$this->serverPort}:{$protocolValue}:{$this->method}:{$obfsValue}:{$user}/?{$paramsStr}";
        return "ssr://" . base64_encode($uri);
    }

    /**
     * 转换为JSON字符串
     *
     * @return string
     */
    public function toJson(): string
    {
        $data = $this->getBaseJsonArray();
        $data['local_port'] = $this->localPort;

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
