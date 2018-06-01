<?php

namespace Milkmeowo\LaravelMns\Console;

use AliyunMNS\Client;
use AliyunMNS\Exception\MnsException;
use Illuminate\Console\Command;

class MnsDeleteQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:mns:delete {queue?} {--c|connection=mns}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '删除 MNS Queue';

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
            $client->deleteQueue($queue);
            $this->info('队列删除成功');
            $this->alert($queue);
        } catch (MnsException $e) {
            $this->error('队列删除失败:' . $e);
        }
    }
}
