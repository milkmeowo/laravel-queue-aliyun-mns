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
use AliyunMNS\Requests\BatchReceiveMessageRequest;
use Illuminate\Console\Command;

class MnsFlushCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'queue:mns:flush {queue?} {--c|connection=mns}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush MNS Queue';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $queue = $this->argument('queue');
        $connection = $this->option('connection');
        $config = config("queue.connections.{$connection}");
        if (!$queue) {
            $queue = $config['queue'];
        }
        $this->alert('队列：'.$queue);
        $client = new Client($config['endpoint'], $config['key'], $config['secret']);
        $queue = $client->getQueueRef($queue);
        $hasMessage = true;
        while ($hasMessage) {
            $this->info('拉取信息中...');

            try {
                $response = $queue->batchPeekMessage(15);
                if ($response->getMessages()) {
                    $hasMessage = true;
                } else {
                    $hasMessage = false;
                }
            } catch (\Exception $e) {
                $this->info('队列中没消息');
                break;
            }
            $response = $queue->batchReceiveMessage(new BatchReceiveMessageRequest(15, 30));
            $handles = [];
            /**
             * @var Message
             */
            foreach ($response->getMessages() as $message) {
                $handles[] = $message->getReceiptHandle();
            }
            $response = $queue->batchDeleteMessage($handles);
            if ($response->isSucceed()) {
                foreach ($handles as $handle) {
                    $this->info(sprintf('信息: %s 删除成功', $handle));
                }
            }
        }
    }
}
