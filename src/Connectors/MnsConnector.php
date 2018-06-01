<?php

/*
 * Laravel-Mns -- 阿里云消息队列（MNS）的 Laravel 适配。
 *
 * This file is part of the milkmeowo/laravel-mns.
 *
 * (c) Milkmeowo <milkmeowo@gmail.com>
 * @link: https://github.com/milkmeowo/laravel-queue-aliyun-mns
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Milkmeowo\LaravelMns\Connectors;

use AliyunMNS\Client as MnsClient;
use Milkmeowo\LaravelMns\MnsQueue;
use Milkmeowo\LaravelMns\Adaptors\MnsAdapter;
use Illuminate\Queue\Connectors\ConnectorInterface;

class MnsConnector implements ConnectorInterface
{
    /**
     * 接口方法，连接器.
     *
     * @param array $config
     *
     * @return \Illuminate\Contracts\Queue\Queue|MnsQueue
     */
    public function connect(array $config)
    {
        $adapter = $this->getAdapter($config);

        return new MnsQueue($adapter, $config['wait_seconds']);
    }

    /**
     * Mns 适配器.
     *
     * @param array $config
     *
     * @return MnsAdapter
     */
    public function getAdapter(array $config)
    {
        $client = $this->getClient($config);

        return new MnsAdapter($client, $config['queue']);
    }

    /**
     * Mns Client.
     *
     * @param array $config
     *
     * @return MnsClient
     */
    public function getClient(array $config)
    {
        return new MnsClient($config['endpoint'], $config['key'], $config['secret']);
    }
}
