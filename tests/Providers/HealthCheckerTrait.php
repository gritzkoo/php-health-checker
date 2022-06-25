<?php

namespace K8s\HealthCheckerTests\Providers;

use InvalidArgumentException;
use K8s\HealthChecker\Utils\Constants;
use TypeError;

trait HealthCheckerTrait
{
    private $livenessContract = [
        'status'  => 'fully functional',
        'version' => 'test',
    ];
    private $readinessContract = [
        'name' => 'test',
        'version' => 'test',
        'integrations' => []
    ];
    private $defaultConfig = [
        'name' => 'test',
        'version' => 'test',
        'integrations' => []
    ];
    public function healthProvider(): array
    {
        return [
            'should run liveness' => [
                function () {
                    return [
                        'method'   => Constants::LIVENESS,
                        'config'   => $this->defaultConfig,
                        'expected' => $this->livenessContract,
                    ];
                }
            ],
            'should run readiness without integrations' => [
                function () {
                    return [
                        'method'       => Constants::READINESS,
                        'config'       => $this->defaultConfig,
                        'expected'     => $this->readinessContract,
                    ];
                }
            ],
            'should throw error because construct is not an array' => [
                function () {
                    return [
                        'method' => Constants::LIVENESS,
                        'throws' => true,
                        'throw_class' => TypeError::class,
                        'throw_message' => Constants::DATA_IS_NOT_A_ARRAY,
                        'config' => null,
                        'expected' => [],
                    ];
                }
            ],
            'should throw error because is missing integrations key' => [
                function () {
                    return [
                        'method' => Constants::LIVENESS,
                        'throws' => true,
                        'throw_class' => InvalidArgumentException::class,
                        'throw_message' => Constants::INTEGRATIONS_NOT_PRESENT,
                        'config' => [],
                        'expected' => [],
                    ];
                }
            ],
            'should throw error because instructions is not a array' => [
                function () {
                    return [
                        'method' => Constants::LIVENESS,
                        'throws' => true,
                        'throw_class' => InvalidArgumentException::class,
                        'throw_message' => Constants::INTEGRATIONS_IS_NOT_A_ARRAY,
                        'config' => [
                            Constants::INTEGRATIONS => null
                        ],
                        'expected' => [],
                    ];
                }
            ],
            'should throw error because instructions element is not a array' => [
                function () {
                    return [
                        'method' => Constants::LIVENESS,
                        'throws' => true,
                        'throw_class' => InvalidArgumentException::class,
                        'throw_message' => Constants::INTEGRATION_ELEMENT_NOT_ARRAY,
                        'config' => [
                            Constants::INTEGRATIONS => [0]
                        ],
                        'expected' => [],
                    ];
                }
            ],
            'should throw error because instructions element is not a array' => [
                function () {
                    return [
                        'method' => Constants::LIVENESS,
                        'throws' => true,
                        'throw_class' => InvalidArgumentException::class,
                        'throw_message' => Constants::HANDLE_IS_NOT_CLOSURE,
                        'config' => [
                            Constants::INTEGRATIONS => [
                                [
                                    Constants::HANDLE => null
                                ]
                            ]
                        ],
                        'expected' => [],
                    ];
                }
            ],
        ];
    }
}
