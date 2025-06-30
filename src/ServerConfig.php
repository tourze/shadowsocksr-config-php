<?php

namespace ShadowsocksR\Config;

use ShadowsocksR\Config\Exception\InvalidConfigurationException;

/**
 * ShadowsocksR服务器配置类
 */
class ServerConfig extends BaseConfig
{
    /**
     * 服务器ID(UUID)
     */
    private string $id;

    /**
     * 创建服务器配置
     *
     * @param string $id 服务器ID(UUID)
     * @param string $server 服务器地址
     * @param int $serverPort 服务器端口
     * @param string $password 密码
     * @param string $method 加密方法
     * @param string|null $protocol 协议插件
     * @param string|null $obfs 混淆插件
     */
    public function __construct(
        string  $id,
        string  $server,
        int     $serverPort,
        string  $password,
        string  $method,
        ?string $protocol = null,
        ?string $obfs = null
    )
    {
        parent::__construct($server, $serverPort, $password, $method);
        $this->id = $id;
        $this->protocol = $protocol;
        $this->obfs = $obfs;
    }

    /**
     * 从JSON字符串创建ServerConfig
     *
     * @param string $json JSON字符串
     * @return self
     * @throws InvalidConfigurationException 如果JSON格式错误或缺少必要字段
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidConfigurationException('JSON格式错误: ' . json_last_error_msg());
        }

        // 检查必要字段
        $requiredFields = ['id', 'server', 'server_port', 'password', 'method'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new InvalidConfigurationException("缺少必要字段: {$field}");
            }
        }

        $protocol = $data['protocol'] ?? null;
        $obfs = $data['obfs'] ?? null;

        $serverConfig = new self(
            $data['id'],
            $data['server'],
            (int)$data['server_port'],
            $data['password'],
            $data['method'],
            $protocol,
            $obfs
        );

        if (isset($data['remarks'])) {
            $serverConfig->setRemarks($data['remarks']);
        }

        if (isset($data['protocol_param'])) {
            $serverConfig->setProtocolParam($data['protocol_param']);
        }

        if (isset($data['obfs_param'])) {
            $serverConfig->setObfsParam($data['obfs_param']);
        }

        return $serverConfig;
    }

    /**
     * 获取服务器ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * 转换为ClientConfig对象
     *
     * @param int $localPort 本地端口
     * @return ClientConfig
     */
    public function toClientConfig(int $localPort = 1080): ClientConfig
    {
        $config = new ClientConfig(
            $this->server,
            $this->serverPort,
            $localPort,
            $this->password,
            $this->method
        );

        if ($this->protocol !== null) {
            $config->setProtocol($this->protocol);
        }

        if ($this->protocolParam !== null) {
            $config->setProtocolParam($this->protocolParam);
        }

        if ($this->obfs !== null) {
            $config->setObfs($this->obfs);
        }

        if ($this->obfsParam !== null) {
            $config->setObfsParam($this->obfsParam);
        }

        if ($this->remarks !== null) {
            $config->setRemarks($this->remarks);
        }

        return $config;
    }

    /**
     * 转换为JSON字符串
     *
     * @return string
     */
    public function toJson(): string
    {
        $data = $this->getBaseJsonArray();
        $data['id'] = $this->id;

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
