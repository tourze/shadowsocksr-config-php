<?php

namespace ShadowsocksR\Config\Exception;

use InvalidArgumentException;

/**
 * 无效配置异常
 * 当提供的配置参数无效时抛出此异常
 */
class InvalidConfigurationException extends InvalidArgumentException
{
}