<?php

declare(strict_types=1);

namespace Gritzkoo\HealthCheckerTests;

use Closure;
use Gritzkoo\HealthChecker\HealthChecker;
use Gritzkoo\HealthCheckerTests\Providers\HealthCheckerTrait;
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
        $this->hand($scenario['expected'], $result, $suit);
        $this->assertEquals($scenario['expected'], $result);
    }
    private function hand(&$exp, &$result, &$suit)
    {
        foreach ($exp as $key => $value) {
            $this->assertArrayHasKey($key, $suit);
            if (is_array($value)) {
                $result[$key] = [];
                $this->hand($value, $result[$key], $suit[$key]);
            } else {
                $result[$key] = $suit[$key];
            }
        }
    }
}
