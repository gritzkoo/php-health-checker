<?php

declare(strict_types=1);

namespace K8s\HealthCheckerTests;

use Closure;
use K8s\HealthChecker\HealthChecker;
use K8s\HealthCheckerTests\Providers\HealthCheckerTrait;
use K8s\HealthCheckerTests\Utils\Defaults;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class HealthCheckerTest extends TestCase
{

    use HealthCheckerTrait;

    /**
     * @dataProvider healthProvider
     */
    public function testHealthChecker(Closure $testSuite): void
    {
        $scenario = $testSuite();
        if ($scenario['throws'] ?? false) {
            $this->expectException($scenario['throw_class']);
            $this->expectExceptionMessage($scenario['throw_message']);
        }
        $checker = new HealthChecker($scenario['config']);
        $suit = $checker->{$scenario['method']}();
        $result = [];
        foreach ($scenario['expected'] as $key => $value) {
            $this->assertArrayHasKey($key, $suit);
            $result[$key] = $value;
        }
        $this->assertEquals($scenario['expected'], $result);
    }
}
