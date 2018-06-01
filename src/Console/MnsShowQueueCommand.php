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

namespace Milkmeowo\LaravelMns\Console;

use AliyunMNS\Client;
use AliyunMNS\Model\Message;
use Illuminate\Console\Command;

class MnsShowQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:mns:show {queue?} {num?} {--c|connection=mns}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '显示 MNS Queue';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $queueName = $this->argument('queue');
        $showNum = $this->argument('num') ?: 15;
        $connection = $this->option('connection');
        $config = config("queue.connections.{$connection}");
        if (!$queueName) {
            $queueName = $config['queue'];
        }
        $client = new Client($config['endpoint'], $config['key'], $config['secret']);
        $queue = $client->getQueueRef($queueName);
        $this->alert('队列:' . $queueName);
        $this->info('拉取信息中...');

        try {
            $response = $queue->batchPeekMessage($showNum);
            if ($messages = $response->getMessages()) {
                /**
                 * @var Message
                 */
                foreach ($messages as $message) {
                    $this->info('------------');
                    $this->info('消息编号' . $message->getMessageId());
                    $this->info('消息正文的 MD5 值' . $message->getMessageBodyMD5());
                    $this->info('消息正文' . $message->getMessageBody());
                    $this->info('消息发送到队列的时间' . $message->getEnqueueTime());
                    $this->info('第一次被消费的时间' . $message->getFirstDequeueTime());
                    $this->info('总共被消费的次数' . $message->getDequeueCount());
                    $this->info('消息的优先级权值' . $message->getPriority());
                    $this->info('------------');
                }
            }
        } catch (\Exception $e) {
            $this->info('队列中没消息');
        }
    }
}
