<?php

namespace ShadowsocksR\Config;

use Shadowsocks\Config\BaseConfig as SSBaseConfig;

/**
 * ShadowsocksR基础配置类
 *
 * 扩展了Shadowsocks基础配置，增加了SSR特有的协议和混淆功能
 */
abstract class BaseConfig extends SSBaseConfig
{
    /**
     * 协议插件
     *
     * 可选值：
     * - origin
     * - auth_sha1_v4
     * - auth_aes128_md5
     * - auth_aes128_sha1
     * - auth_chain_a
     * - auth_chain_b
     */
    protected ?string $protocol = null;

    /**
     * 协议参数
     */
    protected ?string $protocolParam = null;

    /**
     * 混淆插件
     *
     * 可选值：
     * - plain
     * - http_simple
     * - http_post
     * - tls1.2_ticket_auth
     */
    protected ?string $obfs = null;

    /**
     * 混淆参数
     */
    protected ?string $obfsParam = null;

    /**
     * 获取协议类型
     *
     * @return string|null
     */
    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    /**
     * 设置协议类型
     *
     * @param string|null $protocol
     * @return self
     */
    public function setProtocol(?string $protocol): self
    {
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * 获取协议参数
     *
     * @return string|null
     */
    public function getProtocolParam(): ?string
    {
        return $this->protocolParam;
    }

    /**
     * 设置协议参数
     *
     * @param string|null $protocolParam
     * @return self
     */
    public function setProtocolParam(?string $protocolParam): self
    {
        $this->protocolParam = $protocolParam;
        return $this;
    }

    /**
     * 获取混淆类型
     *
     * @return string|null
     */
    public function getObfs(): ?string
    {
        return $this->obfs;
    }

    /**
     * 设置混淆类型
     *
     * @param string|null $obfs
     * @return self
     */
    public function setObfs(?string $obfs): self
    {
        $this->obfs = $obfs;
        return $this;
    }

    /**
     * 获取混淆参数
     *
     * @return string|null
     */
    public function getObfsParam(): ?string
    {
        return $this->obfsParam;
    }

    /**
     * 设置混淆参数
     *
     * @param string|null $obfsParam
     * @return self
     */
    public function setObfsParam(?string $obfsParam): self
    {
        $this->obfsParam = $obfsParam;
        return $this;
    }

    /**
     * 获取基础JSON数据数组
     */
    protected function getBaseJsonArray(): array
    {
        $data = parent::getBaseJsonArray();

        if ($this->protocol !== null) {
            $data['protocol'] = $this->protocol;
        }

        if ($this->protocolParam !== null) {
            $data['protocol_param'] = $this->protocolParam;
        }

        if ($this->obfs !== null) {
            $data['obfs'] = $this->obfs;
        }

        if ($this->obfsParam !== null) {
            $data['obfs_param'] = $this->obfsParam;
        }

        return $data;
    }
}
