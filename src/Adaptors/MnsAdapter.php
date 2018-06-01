<?php

namespace Milkmeowo\LaravelMns\Adaptors;

use AliyunMNS\AsyncCallback;
use AliyunMNS\Client as MnsClient;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Model\QueueAttributes;
use AliyunMNS\Queue;
use AliyunMNS\Requests\BatchDeleteMessageRequest;
use AliyunMNS\Requests\BatchPeekMessageRequest;
use AliyunMNS\Requests\BatchReceiveMessageRequest;
use AliyunMNS\Requests\BatchSendMessageRequest;
use AliyunMNS\Requests\CreateQueueRequest;
use AliyunMNS\Requests\ListQueueRequest;
use AliyunMNS\Requests\SendMessageRequest;
use AliyunMNS\Responses\BatchDeleteMessageResponse;
use AliyunMNS\Responses\BatchPeekMessageResponse;
use AliyunMNS\Responses\BatchReceiveMessageResponse;
use AliyunMNS\Responses\BatchSendMessageResponse;
use AliyunMNS\Responses\ChangeMessageVisibilityResponse;
use AliyunMNS\Responses\GetQueueAttributeResponse;
use AliyunMNS\Responses\MnsPromise;
use AliyunMNS\Responses\PeekMessageResponse;
use AliyunMNS\Responses\ReceiveMessageResponse;
use AliyunMNS\Responses\SendMessageResponse;
use AliyunMNS\Responses\SetQueueAttributeResponse;

/**
 * Class MNSAdapter
 *
 * @method string getQueueName()
 * @method SetQueueAttributeResponse setAttribute(QueueAttributes $attributes)
 * @method MnsPromise setAttributeAsync(QueueAttributes $attributes, AsyncCallback $callback = null)
 * @method GetQueueAttributeResponse getAttribute()
 * @method MnsPromise getAttributeAsync(AsyncCallback $callback = null)
 * @method SendMessageResponse sendMessage(SendMessageRequest $request)
 * @method MnsPromise sendMessageAsync(SendMessageRequest $request, AsyncCallback $callback = null)
 * @method PeekMessageResponse peekMessage()
 * @method MnsPromise peekMessageAsync(AsyncCallback $callback = null)
 * @method ReceiveMessageResponse receiveMessage(int $waitSeconds = null)
 * @method MnsPromise receiveMessageAsync(AsyncCallback $callback = null)
 * @method ReceiveMessageResponse deleteMessage(string $receiptHandle)
 * @method MnsPromise deleteMessageAsync(string $receiptHandle, AsyncCallback $callback = null)
 * @method ChangeMessageVisibilityResponse changeMessageVisibility(string $receiptHandle, int $visibilityTimeout)
 * @method BatchSendMessageResponse batchSendMessage(BatchSendMessageRequest $request)
 * @method MnsPromise batchSendMessageAsync(BatchSendMessageRequest $request, AsyncCallback $callback = null)
 * @method BatchReceiveMessageResponse batchReceiveMessage(BatchReceiveMessageRequest $request)
 * @method MnsPromise batchReceiveMessageAsync(BatchReceiveMessageRequest $request, AsyncCallback $callback = null)
 * @method BatchPeekMessageResponse batchPeekMessage(BatchPeekMessageRequest $request)
 * @method MnsPromise batchPeekMessageAsync(BatchPeekMessageRequest $request, AsyncCallback $callback = null)
 * @method BatchDeleteMessageResponse batchDeleteMessage(BatchDeleteMessageRequest $request)
 * @method MnsPromise batchDeleteMessageAsync(BatchDeleteMessageRequest $request, AsyncCallback $callback = null)
 */
class MnsAdapter
{
    /**
     * @var string 适配的阿里云消息服务 SDK 版本，仅用作记录。
     *
     * @see https://help.aliyun.com/document_detail/32381.html
     */
    const ADAPTER_TO_ALIYUN_MNS_SDK_VERSION = '1.3.5@2017-06-06';
    /**
     * Aliyun MNS Client
     *
     * @var MnsClient $client
     */
    private $client;
    /**
     * Aliyun MNS SDK Queue.
     *
     * @var Queue $queue
     */
    private $queue;

    /**
     * MnsAdapter constructor.
     * @param MnsClient $client
     * @param string $queue
     */
    public function __construct(MnsClient $client, string $queue)
    {
        $this->client = $client;

        $this->useQueue($queue);
    }

    /**
     * 转化 \AliyunMNS\Client 对象，
     * 可以通过本对象直接访问（而无需通过 \AliyunMNS\Client 对象构建）。
     *
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->queue, $method], $parameters);
    }

    /**
     * 将队列设定为特定队列。
     *
     * @param string $queue
     *
     * @return self
     */
    public function useQueue($queue)
    {
        if (null != $queue) {
            $this->queue = $this->client->getQueueRef($queue);
        }
        return $this;
    }

    /**
     * 创建队列
     *
     * @param string $queueName 队列名
     */
    public function createQueue($queueName)
    {
        try {
            $request = new CreateQueueRequest($queueName);
            $response = $this->client->createQueue($request);
            return $response->isSucceed();
        } catch (MnsException $e) {
        }
    }

    /**
     * 异步创建队列
     *
     * @param string $queueName 队列名
     * @param AsyncCallback|null $callback 异步回调
     */
    public function createQueueAsync($queueName, AsyncCallback $callback = null)
    {
        try {
            $request = new CreateQueueRequest($queueName);
            $this->client->createQueueAsync($request, $callback);
        } catch (MnsException $e) {
        }
    }

    /**
     * 获取队列列表
     *
     * @param null $retNum 单次请求结果的最大返回个数，可以取1-1000范围内的整数值，默认值为1000。
     * @param null $prefix 按照该前缀开头的 queueName 进行查找。
     * @param null $marker 请求下一个分页的开始位置，一般从上次分页结果返回的NextMarker获取。
     * @return \AliyunMNS\Responses\ListQueueResponse
     */
    public function listQueue($retNum = null, $prefix = null, $marker = null)
    {
        try {
            $request = new ListQueueRequest($retNum, $prefix, $marker);
            return $this->client->listQueue($request);
        } catch (MnsException $e) {

        }
    }

    /**
     * 获取队列列表
     *
     * @param int $retNum 单次请求结果的最大返回个数，可以取1-1000范围内的整数值，默认值为1000。
     * @param string $prefix 按照该前缀开头的 queueName 进行查找。
     * @param string $marker 请求下一个分页的开始位置，一般从上次分页结果返回的NextMarker获取。
     * @param AsyncCallback|NULL $callback
     * @return mixed
     */
    public function listQueueAsync($retNum = NULL, $prefix = NULL, $marker = NULL, AsyncCallback $callback = NULL)
    {
        try {
            $request = new ListQueueRequest($retNum, $prefix, $marker);
            return $this->client->listQueueAsync($request, $callback);
        } catch (MnsException $e) {

        }

    }

    /**
     * 删除队列
     *
     * @param string $queueName 队列名
     */
    public function deleteQueue($queueName)
    {
        try {
            $response = $this->client->deleteQueue($queueName);
            return $response->isSucceed();
        } catch (MnsException $e) {
        }
    }

    /**
     * 异步删除队列
     *
     * @param string $queueName 队列名
     * @param AsyncCallback|NULL $callback
     */
    public function deleteQueueAsync($queueName, AsyncCallback $callback = NULL)
    {
        try {
            $this->client->deleteQueueAsync($queueName, $callback);
        } catch (MnsException $e) {
        }
    }
}
