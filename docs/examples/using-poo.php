<?php

namespace App\Services;

use App\Api\YourIntegrationA;
use App\Api\YourIntegrationb;
use Gritzkoo\HealthChecker\HealthChecker;

class Healthcheck
{
    /**
     * Holds an instance of Gritzkoo\HealthChecker\HealthChecker with configurations
     *
     * @var \Gritzkoo\HealthChecker\HealthChecker
     */
    private $checker;
    /**
     * injecting external dependencies
     *
     * @param YourIntegrationA $api1
     * @param YourIntegrationB $api2
     */
    public function __construct(
        YourIntegrationA $api1,
        YourIntegrationB $api2
    ) {
        $this->checker = new HealthChecker([
            'name' => 'My application',
            'version' => $this->getVersion(),
            'integrations' => [
                [
                    'name' => 'YourIntegrationA',
                    'handle' => function () use ($api1) {
                        // is just a function you write to test something and return
                        // an instance of \Gritzkoo\HealthChecker\Check
                        return $api1->test();
                    }
                ],
                [
                    'name' => 'YourIntegrationB',
                    'handle' => function () use ($api2) {
                        // is just a function you write to test something and return
                        // an instance of \Gritzkoo\HealthChecker\Check
                        return $api2->test();
                    }
                ],
            ]
        ]);
    }
    /**
     * rev.txt is a file created by a commad line like:
     * git show -s --format="%ai %H %s %aN" HEAD > rev.txt
     * when you build or deploy you app and its content will be like:
     * |date| commit hash| commit message| author|
     * 2022-06-25 11:06:30 -0300 e3b9dde73153b0a63e80153a6d526b607fc559d5 your awesome commit message prettyprincess
     */
    private function getVersion()
    {
        $version = 'file not found!';
        $revFile = 'path/to/rev.txt';
        if (file_exists($revFile)) {
            $version = file_get_contents($revFile);
        }
        return $version;
    }
    /**
     * return liveness action provided in Gritzkoo\HealthChecker\HealthChecker
     *
     * @return array
     */
    public function liveness()
    {
        return $this->checker->liveness();
    }
    /**
     * return readiness action provided in Gritzkoo\HealthChecker\HealthChecker
     *
     * @return array
     */
    public function readiness()
    {
        return $this->checker->readiness();
    }
}
