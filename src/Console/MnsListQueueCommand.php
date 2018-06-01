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
use Illuminate\Console\Command;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Requests\ListQueueRequest;

class MnsListQueueCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'queue:mns:list {--p|prefix} {--connection=mns}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List MNS Queue';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $connection = $this->option('connection');
        $config = config("queue.connections.{$connection}");

        $client = new Client($config['endpoint'], $config['key'], $config['secret']);

        $prefix = null;
        if ($this->option('prefix')) {
            $prefix = $this->ask('请填写prefix');
        }
        $this->listQueue($client, $prefix);
    }

    /**
     * 列出队列内容.
     *
     * @param Client $client MNS Client
     * @param null   $prefix 前缀
     * @param null   $marker marker
     */
    public function listQueue(Client $client, $prefix = null, $marker = null)
    {
        $request = new ListQueueRequest(null, $prefix, $marker);

        try {
            $res = $client->listQueue($request);
            $this->info('查询队列成功');
            foreach ($res->getQueueNames() as $queueName) {
                $this->info($queueName);
            }
            $marker = $res->getNextMarker();
            if ($marker) {
                $this->question('---下一页:[' . base64_decode($marker) . ']---');
                $this->listQueue($client, $prefix, $marker);
            }
        } catch (MnsException $e) {
            $this->error('查询队列失败:' . $e);
        }
    }
}
