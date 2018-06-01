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

namespace Milkmeowo\LaravelMns\Test;

use AliyunMNS\Exception\MessageNotExistException;
use AliyunMNS\Requests\SendMessageRequest;
use AliyunMNS\Responses\ReceiveMessageResponse;
use AliyunMNS\Responses\SendMessageResponse;
use Carbon\Carbon;
use Milkmeowo\LaravelMns\Adaptors\MnsAdapter;
use Milkmeowo\LaravelMns\Jobs\MnsJob;
use Milkmeowo\LaravelMns\MnsQueue;
use Mockery as m;

class MnsQueueTest extends TestCase
{
    private $queueName;
    private $waitSeconds;
    private $mockedDelay;
    private $mockedJob;
    private $mockedData;
    private $mockedPayload;
    private $mnsAdapter;
    private $mockedSendRequest;
    private $mockedSendResponse;
    private $mockedMessageId;
    private $mockedReceiveResponse;
    private $mnsClient;
    private $mnsQueueClient;

    public function tearDown()
    {
        parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();

        $this->queueName = 'default';
        $this->waitSeconds = 30;
        $this->mockedDelay = 10;

        $this->mockedJob = 'job';
        $this->mockedData = ['data'];
        $this->mockedPayload = json_encode([
            'job'  => $this->mockedJob,
            'data' => $this->mockedData,
        ]);

        $this->mockedMessageId = '5F290C926D472878-2-14D9529A8FA-200000001';

        $this->mockedSendRequest = new SendMessageRequest($this->mockedPayload);
        $this->mockedSendResponse = m::mock(SendMessageResponse::class);

        $this->mockedReceiveResponse = m::mock(ReceiveMessageResponse::class);

        $this->mnsClient = m::mock(\AliyunMNS\Client::class);
        $this->mnsQueueClient = m::mock(\AliyunMNS\Queue::class);

        $this->mnsClient
            ->shouldReceive('getQueueRef')
            ->with($this->queueName)
            ->andReturn($this->mnsQueueClient);
        $this->mnsQueueClient
            ->shouldReceive('getQueueName')
            ->andReturn($this->queueName);

        // 构造 mnsAdapter
        $this->mnsAdapter = new MnsAdapter($this->mnsClient, $this->queueName);

        // 构造 getMessageId 方法
        $this->mockedSendResponse
            ->shouldReceive('getMessageId')
            ->withNoArgs()
            ->andReturn($this->mockedMessageId);
    }

    public function testPushProperlyPushesJobsOntoMns()
    {
        // 构造Mns队列对象
        $queue = m::mock(MnsQueue::class.'[createPayload]', [$this->mnsAdapter, $this->waitSeconds])
            ->shouldAllowMockingProtectedMethods();

        // 构造 createPayload 传 job data 应该输出payload
        $queue->shouldReceive('createPayload')
            ->once()
            ->with($this->mockedJob, $this->mockedData)
            ->andReturn($this->mockedPayload);

        // 构造 sendMessage 方法
        $this->mnsQueueClient
            ->shouldReceive('sendMessage')
            ->once()
            ->with(m::type(SendMessageRequest::class))
            ->andReturn($this->mockedSendResponse);

        $id = $queue->push($this->mockedJob, $this->mockedData, $this->queueName);

        $this->assertEquals($this->mockedMessageId, $id);
    }

    public function testLaterProperlyPushesDelayedOntoMns()
    {
        $queue = m::mock(MnsQueue::class.'[createPayload, secondsUntil]', [$this->mnsAdapter, $this->waitSeconds])
            ->shouldAllowMockingProtectedMethods();

        $queue->shouldReceive('createPayload')
            ->once()
            ->with($this->mockedJob, $this->mockedData)
            ->andReturn($this->mockedPayload);
        $queue->shouldReceive('secondsUntil')
            ->once()
            ->with($this->mockedDelay)
            ->andReturn($this->mockedDelay);

        // 构造 sendMessage 方法
        $this->mnsQueueClient
            ->shouldReceive('sendMessage')
            ->once()
            ->with(m::type(SendMessageRequest::class))
            ->andReturn($this->mockedSendResponse);

        $id = $queue->later($this->mockedDelay, $this->mockedJob, $this->mockedData, $this->queueName);
        $this->assertEquals($this->mockedMessageId, $id);
    }

    public function testLaterProperlyPushesDelayedUsingDatetimeOntoMns()
    {
        $now = Carbon::now();
        $queue = m::mock(MnsQueue::class, [$this->mnsAdapter, $this->waitSeconds])->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $queue->shouldReceive('createPayload')
            ->once()
            ->with($this->mockedJob, $this->mockedData)
            ->andReturn($this->mockedPayload);
        $queue->shouldReceive('secondsUntil')
            ->once()
            ->with($now)
            ->andReturn(3600);

        // 构造 sendMessage 方法
        $this->mnsQueueClient
            ->shouldReceive('sendMessage')
            ->once()
            ->with(m::type(SendMessageRequest::class))
            ->andReturn($this->mockedSendResponse);
        $id = $queue->later($now->addSeconds(3600), $this->mockedJob, $this->mockedData, $this->queueName);
        $this->assertEquals($this->mockedMessageId, $id);
    }

    public function testPopProperlyPopOffFromJobMns()
    {
        $queue = m::mock(MnsQueue::class, [$this->mnsAdapter, $this->waitSeconds])->makePartial();
        $queue->setContainer(m::mock(\Illuminate\Container\Container::class));

        // 构造 receiveMessage 方法
        $this->mnsQueueClient
            ->shouldReceive('receiveMessage')
            ->once()
            ->with($this->waitSeconds)
            ->andReturn($this->mockedReceiveResponse);
        $job = $queue->pop($this->queueName);
        $this->assertInstanceOf(MnsJob::class, $job);
    }

    public function testPopProperlyReturnsNullWhenMnsHasNoActivelyMessage()
    {
        $queue = m::mock(MnsQueue::class, [$this->mnsAdapter, $this->waitSeconds])->makePartial();
        $queue->setContainer(m::mock(\Illuminate\Container\Container::class));
        $this->mnsQueueClient->shouldReceive('receiveMessage')
            ->once()
            ->with($this->waitSeconds)
            ->andThrow(new MessageNotExistException(404, 'No message can get.'));
        $result = $queue->pop($this->queueName);
        $this->assertNull($result);
    }

    public function testGetQueueCanResolveWantedQueueNameOrReturnDefault()
    {
        $queue = new MnsQueue($this->mnsAdapter, $this->waitSeconds);
        $this->assertEquals($this->queueName, $queue->getQueue(null));
        $this->assertEquals('somequeue', $queue->getQueue('somequeue'));
    }
}
