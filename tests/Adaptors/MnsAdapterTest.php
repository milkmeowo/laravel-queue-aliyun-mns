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

namespace Milkmeowo\LaravelMns\Test\Adaptors;

use AliyunMNS\Client;
use AliyunMNS\Queue;
use Milkmeowo\LaravelMns\Adaptors\MnsAdapter;
use Milkmeowo\LaravelMns\Test\TestCase;
use Mockery as m;

class MnsAdapterTest extends TestCase
{
    public function testUseQueueProperlySetQueueName()
    {
        $defaultQueueName = 'default';
        $anotherQueueName = 'anotherName';
        $mnsClient = m::mock(Client::class)->makePartial();
        $mnsQueueClient = m::mock(Queue::class);
        $mnsClient->shouldReceive('getQueueRef')
                  ->twice()
                  ->with(m::anyOf($defaultQueueName, $anotherQueueName))
                  ->andReturn($mnsQueueClient);

        $mnsQueueClient->shouldReceive('getQueueName')
            ->twice()
            ->andReturn($defaultQueueName, $anotherQueueName);

        $adapterUnderTest = new MnsAdapter($mnsClient, $defaultQueueName);

        $this->assertEquals('default', $adapterUnderTest->getQueueName());

        $adapterUnderTest->useQueue('anotherName');

        $this->assertEquals('anotherName', $adapterUnderTest->getQueueName());
    }
}
