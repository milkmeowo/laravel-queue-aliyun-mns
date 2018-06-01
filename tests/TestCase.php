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

namespace Milkmeowo\LaravelMns\Test;

use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function tearDown()
    {
        // 因为运行在 strict 模式下的 phpunit，会将不包含 phpunit 断言的测试方法
        // 标记为 risky。这里简单的在测试方法执行后把 mockery 的预期全部转化成
        // phpunit 的断言，通常不直接包含 phpunit 断言的测试，基本都是 mockery 预期。
        // 测试类需要继承这里， m::close() 的时候替换为 parent::tearDown()。
        parent::tearDown();
        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount(
                $container->mockery_getExpectationCount()
            );
        }
        Mockery::close();
    }
}
