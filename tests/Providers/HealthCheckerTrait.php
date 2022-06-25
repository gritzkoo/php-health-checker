<?php

namespace K8s\HealthCheckerTests\Providers;

use Exception;
use InvalidArgumentException;
use K8s\HealthChecker\Check;
use K8s\HealthChecker\Utils\Constants;
use TypeError;

trait HealthCheckerTrait
{
    private $livenessContract = [
        Constants::STATUS  => Constants::FULLY_FUNCTIONAL,
        Constants::VERSION => 'test',
    ];
    private $readinessContract = [
        Constants::NAME => 'test',
        Constants::VERSION => 'test',
        Constants::STATUS => true,
        Constants::INTEGRATIONS => []
    ];
    private $defaultConfig = [
        Constants::NAME => 'test',
        Constants::VERSION => 'test',
        Constants::INTEGRATIONS => []
    ];
    /**
     * Package default test provider
     *
     * @return array
     */
    public function healthProvider(): array
    {
        return [
            // basic testing structure responses ==================================================
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
            // testing integrations
            'should run readiness with a integration' => [
                function () {
                    $conf = $this->defaultConfig;
                    $conf['integrations'][] = [
                        'name' => 'test',
                        'handle' => function () {
                            return new Check([
                                'url' => 'test',
                            ]);
                        },
                    ];
                    $exp = $this->readinessContract;
                    $exp['integrations'][] = [
                        'name' => 'test',
                        'status' => true,
                        'url' => 'test',
                    ];
                    return [
                        'method'       => Constants::READINESS,
                        'config'       => $conf,
                        'expected'     => $exp,
                    ];
                }
            ],
            'should run readiness with a integration and validate return type' => [
                function () {
                    $conf = $this->defaultConfig;
                    $conf['integrations'][] = [
                        'name' => 'test',
                        'handle' => function () {
                            return 'just a test';
                        },
                    ];
                    $exp = $this->readinessContract;
                    $exp['status'] = false;
                    $exp['integrations'][] = [
                        'name' => 'test',
                        'status' => false,
                        'url' => '',
                        'error' => Constants::INVALID_CALLBACK_RESPONSE,
                    ];
                    return [
                        'method'       => Constants::READINESS,
                        'config'       => $conf,
                        'expected'     => $exp,
                    ];
                }
            ],
            'should run readiness with a integration and convert Check erro exception to string' => [
                function () {
                    $err = new Exception("test");
                    $conf = $this->defaultConfig;
                    $conf['integrations'][] = [
                        'name' => 'test',
                        'handle' => function () use ($err) {
                            return new Check([
                                'url' => 'test',
                                'error' => $err,
                            ]);
                        },
                    ];
                    $exp = $this->readinessContract;
                    $exp['status'] = false;
                    $exp['integrations'][] = [
                        'name' => 'test',
                        'status' => false,
                        'url' => 'test',
                        'error' => $err->__toString(),
                    ];
                    return [
                        'method'       => Constants::READINESS,
                        'config'       => $conf,
                        'expected'     => $exp,
                    ];
                }
            ],
            'should run readiness with a integration and catch a external throw exception' => [
                function () {
                    $err = new Exception("test");
                    $conf = $this->defaultConfig;
                    $conf['integrations'][] = [
                        'name' => 'test',
                        'handle' => function () use ($err) {
                            throw $err;
                        },
                    ];
                    $exp = $this->readinessContract;
                    $exp['status'] = false;
                    $exp['integrations'][] = [
                        'name' => 'test',
                        'status' => false,
                        'url' => '',
                        'error' => $err->__toString(),
                    ];
                    return [
                        'method'       => Constants::READINESS,
                        'config'       => $conf,
                        'expected'     => $exp,
                    ];
                }
            ],
            // error section ======================================================================
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
                            Constants::INTEGRATIONS => [1, 2, 3, 4]
                        ],
                        'expected' => [],
                    ];
                }
            ],
            'should throw error because instructions element handle is not a closure' => [
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
