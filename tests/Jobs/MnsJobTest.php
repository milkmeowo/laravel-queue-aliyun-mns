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

namespace Milkmeowo\LaravelMns\Test\Jobs;

use Mockery as m;
use AliyunMNS\Exception\MnsException;
use Milkmeowo\LaravelMns\Jobs\MnsJob;
use Milkmeowo\LaravelMns\Test\TestCase;
use Milkmeowo\LaravelMns\Adaptors\MnsAdapter;
use AliyunMNS\Responses\ReceiveMessageResponse;

class MnsJobTest extends TestCase
{
    private $queue;
    private $receiptHandle;
    private $delay;
    private $mockedJob;
    private $mockedData;
    private $mockedPayload;
    private $mockedContainer;
    private $mockedResponse;
    private $mockedAdapter;
    private $msgId;

    public function setUp()
    {
        parent::setUp();

        $this->queue = 'queue';
        $this->receiptHandle = 'ReceiptHandleXXXX';
        $this->delay = 3600;
        $this->msgId = 'msgId';

        $this->mockedJob = 'job';
        $this->mockedData = ['data'];
        $this->mockedPayload = json_encode(['job' => $this->mockedJob, 'data' => $this->mockedData]);

        $this->mockedContainer = m::mock(\Illuminate\Container\Container::class);
        $this->mockedResponse = m::mock(ReceiveMessageResponse::class);
        $this->mockedAdapter = m::mock(MnsAdapter::class);
    }

    public function testFireProperlyCallJobHandler()
    {
        $job = $this->getJob();
        $job->getContainer()->shouldReceive('make')
            ->once()
            ->with('job')
            ->andReturn($handler = m::mock('stdClass'));
        $this->mockedResponse->shouldReceive('getMessageBody')
                             ->andReturn($this->mockedPayload);
        $handler->shouldReceive('fire')
                ->once()
                ->with($job, ['data']);
        $job->fire();
    }

    public function testDeleteProperlyRemovesFromMns()
    {
        $job = $this->getJob();
        $this->mockedResponse->shouldReceive('getReceiptHandle')
                             ->once()
                             ->andReturn($this->receiptHandle);
        $this->mockedAdapter->shouldReceive('deleteMessage')
                            ->with($this->receiptHandle)
                            ->andReturn(true);
        $job->delete($this->receiptHandle);
        $this->assertTrue($job->isDeleted());
    }

    public function testDeleteProperlyRemovesFromMnsFailed()
    {
        $job = $this->getJob();
        $this->mockedResponse->shouldReceive('getReceiptHandle')
            ->once()
            ->andReturn($this->receiptHandle);
        $this->mockedAdapter->shouldReceive('deleteMessage')
            ->with($this->receiptHandle)
            ->andThrow(new MnsException(404, 'not found'));
        $job->delete($this->receiptHandle);
        $this->assertFalse($job->isDeleted());
    }

    public function testReleaseProperlySetVisibleTimeToMns()
    {
        $job = $this->getJob();
        $this->mockedResponse->shouldReceive('getReceiptHandle')
                             ->twice()
                             ->andReturn($this->receiptHandle);
        $this->mockedAdapter->shouldReceive('changeMessageVisibility')
                            ->once()
                            ->with($this->receiptHandle, $this->delay)
                            ->andReturn('true');
        $job->release($this->delay);
        $this->mockedResponse->shouldReceive('getNextVisibleTime')->once();
        $this->mockedAdapter->shouldReceive('changeMessageVisibility')->once();
        $job->release();
        $this->assertTrue($job->isReleased());
    }

    public function testAttemptsCanGetDequeueCount()
    {
        $job = $this->getJob();
        $this->mockedResponse->shouldReceive('getDequeueCount')->andReturn(5);
        $attempts = $job->attempts();
        $this->assertEquals(5, $attempts);
    }

    public function testGetJobId()
    {
        $job = $this->getJob();
        $this->mockedResponse->shouldReceive('getMessageId')->andReturn($this->msgId);

        $this->assertEquals($this->msgId, $job->getJobId());
    }

    private function getJob()
    {
        return new MnsJob(
            $this->mockedContainer,
            $this->mockedAdapter,
            $this->queue,
            $this->mockedResponse
        );
    }
}
