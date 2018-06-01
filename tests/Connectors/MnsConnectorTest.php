<?php

namespace Milkmeowo\LaravelMns\Test\Connectors;

use Milkmeowo\LaravelMns\Connectors\MnsConnector;
use Milkmeowo\LaravelMns\MnsQueue;
use Milkmeowo\LaravelMns\Test\TestCase;
use Mockery as m;

class MnsConnectorTest extends TestCase
{
    public function testConenectProperlyReturnsMnsQueue()
    {
        $config = [
            'endpoint' => 'endpoint',
            'key' => 'key',
            'secret' => 'secret',
            'queue' => 'queue',
            'wait_seconds' => 30,
        ];

        $connector = new MnsConnector();
        $connection = $connector->connect($config);

        $this->assertInstanceOf(MnsQueue::class, $connection);

       $this->assertEquals('queue', $connection->getQueue(null));
       $this->assertEquals('otherQueue', $connection->getQueue('otherQueue'));
    }
}
