<?php

declare(strict_types=1);

namespace K8s\HealthCheckerTests;

use Closure;
use K8s\HealthChecker\HealthChecker;
use K8s\HealthCheckerTests\Providers\HealthCheckerTrait;
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
                $tmpRslt = &$result[$key];
                $tmpSuit = &$suit[$key];
                $this->hand($value, $tmpRslt, $tmpSuit);
            } else {
                $result[$key] = $suit[$key];
            }
        }
    }
}
