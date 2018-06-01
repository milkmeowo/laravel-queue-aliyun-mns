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
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Requests\CreateQueueRequest;
use Illuminate\Console\Command;

class MnsCreateQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:mns:create {queue?} {--c|connection=mns}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建 MNS Queue';

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
            $queue = $this->ask('请输入队列名称');
        }

        try {
            $client = new Client($config['endpoint'], $config['key'], $config['secret']);
            $request = new CreateQueueRequest($queue);
            $client->createQueue($request);
            $this->info('队列创建成功');
            $this->alert($queue);
        } catch (MnsException $e) {
            $this->error('队列创建失败:'.$e);
        }
    }
}
