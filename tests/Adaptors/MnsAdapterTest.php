<?php

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
            ->andReturn($defaultQueueName,$anotherQueueName);

        $adapterUnderTest = new MnsAdapter($mnsClient, $defaultQueueName);

        $this->assertEquals('default',$adapterUnderTest->getQueueName());

        $adapterUnderTest->useQueue('anotherName');

        $this->assertEquals('anotherName',$adapterUnderTest->getQueueName());

    }
}
