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

namespace Milkmeowo\LaravelMns\Test\Connectors;

use Milkmeowo\LaravelMns\MnsQueue;
use Milkmeowo\LaravelMns\Test\TestCase;
use Milkmeowo\LaravelMns\Connectors\MnsConnector;

class MnsConnectorTest extends TestCase
{
    public function testConenectProperlyReturnsMnsQueue()
    {
        $config = [
            'endpoint'     => 'endpoint',
            'key'          => 'key',
            'secret'       => 'secret',
            'queue'        => 'queue',
            'wait_seconds' => 30,
        ];

        $connector = new MnsConnector();
        $connection = $connector->connect($config);

        $this->assertInstanceOf(MnsQueue::class, $connection);

        $this->assertEquals('queue', $connection->getQueue(null));
        $this->assertEquals('otherQueue', $connection->getQueue('otherQueue'));
    }
}
