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

namespace Milkmeowo\LaravelMns;

use AliyunMNS\Exception\MessageNotExistException;
use AliyunMNS\Requests\SendMessageRequest;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Queue;
use Milkmeowo\LaravelMns\Adaptors\MnsAdapter;
use Milkmeowo\LaravelMns\Jobs\MnsJob;

class MnsQueue extends Queue implements QueueContract
{
    /**
     * Mns 适配器.
     *
     * @var MnsAdapter
     */
    protected $adapter;
    /**
     * 默认队列.
     *
     * @var string
     */
    protected $default;
    /**
     * 等待秒数.
     *
     * @var null
     */
    private $waitSeconds;

    /**
     * MnsQueue 构造.
     *
     * @param MnsAdapter $adapter     Mns 适配器
     * @param int        $waitSeconds 等待秒数
     */
    public function __construct(MnsAdapter $adapter, int $waitSeconds = 0)
    {
        $this->adapter = $adapter;
        $this->default = $this->adapter->getQueueName();
        $this->waitSeconds = $waitSeconds;
    }

    /**
     * 获取队列长度.
     *
     * @param null $queue
     *
     * @throws \Exception
     *
     * @return int|void
     */
    public function size($queue = null)
    {
        throw new \Exception('The size method is not support for aliyun-mns');
    }

    /**
     * 推送新的 Job 进队列.
     *
     * @param string|object $job   任务
     * @param mixed         $data  数据
     * @param string        $queue 队列
     *
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        $payload = $this->createPayload($job, $data);

        return $this->pushRaw($payload, $queue);
    }

    /**
     * 推送 raw payload 进队列.
     *
     * @param string $payload 数据
     * @param string $queue   队列
     * @param array  $options 选项
     *
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $message = new SendMessageRequest($payload);

        $queue = $this->getQueue($queue);

        $response = $this->adapter->useQueue($queue)->sendMessage($message);

        return $response->getMessageId();
    }

    /**
     *  获取默认队列名（如果当前队列名为 null）。
     *
     * @param $queue
     *
     * @return string
     */
    public function getQueue($queue)
    {
        return $queue ?: $this->default;
    }

    /**
     * 延迟推送 Job 进队列.
     *
     * @param \DateTimeInterface|\DateInterval|int $delay 延迟时间 秒
     * @param string|object                        $job   任务
     * @param mixed                                $data  数据
     * @param string                               $queue 队列
     *
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        $seconds = $this->secondsUntil($delay);
        $payload = $this->createPayload($job, $data);
        $queue = $this->getQueue($queue);

        $message = new SendMessageRequest($payload, $seconds);

        $response = $this->adapter->useQueue($queue)->sendMessage($message);

        return $response->getMessageId();
    }

    /**
     * 从队列弹出下一个 Job.
     *
     * @param string $queue 队列
     *
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        try {
            $response = $this->adapter->useQueue($queue)->receiveMessage($this->waitSeconds);
        } catch (MessageNotExistException $e) {
            $response = null;
        }
        if ($response) {
            return new MnsJob($this->container, $this->adapter, $queue, $response);
        }
    }

    /**
     * 获取 Mns 适配器.
     *
     * @return MnsAdapter
     */
    public function getMns()
    {
        return $this->adapter;
    }
}
