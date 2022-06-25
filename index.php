<?php
require 'vendor/autoload.php';

use K8s\HealthChecker\HealthChecker;
use K8s\HealthChecker\Check;

$checker = new HealthChecker([
    'name' => 'test',
    'version' => 'v1.0.0',
    'integrations' => [
        [
            'name' => 'github integration1',
            'handle' => function () {
                return new Check([
                    'error' => null,
                    'url' => 'https://github.com/status1'
                ]);
            }
        ],
        [
            'name' => 'github integration2',
            'handle' => function () {
                return new Check([
                    'error' => new Exception("error"),
                    'url' => 'https://github.com/status2'
                ]);
            }
        ],
        [
            'name' => 'github integration3',
            'handle' => function () {
                return new Check([
                    'error' => ['asdf' => 'sdsdf'],
                    'url' => 'https://github.com/status3'
                ]);
            }
        ],
    ]
]);

echo json_encode($checker->readiness(), JSON_PRETTY_PRINT);
