<?php

namespace K8s\HealthChecker;

use Closure;
use InvalidArgumentException;
use K8s\HealthChecker\Utils\Constants;

class HealthChecker
{
    private $name;
    private $version;
    private $integrations = [];

    /**
     * Create an instance of HealthChecker
     *
     * @param array $data
     * @throws InvalidArgumentException
     */
    public function __construct(array $data = [])
    {
        $this->validate($data);
        $this->name    = !empty($data[Constants::NAME]) ? $data[Constants::NAME] : '';
        $this->version = !empty($data[Constants::VERSION]) ? $data[Constants::VERSION] : '';
    }
    /**
     * Liveness will return simple status and version response
     *
     * @return array
     */
    public function liveness()
    {
        return [
            Constants::STATUS  => Constants::FULLY_FUNCTIONAL,
            Constants::VERSION => $this->version
        ];
    }
    /**
     * Undocumented function
     *
     * @return array
     */
    public function readiness()
    {
        $begin = microtime(true);
        $result = [
            Constants::NAME         => $this->name,
            Constants::VERSION      => $this->version,
            Constants::STATUS       => true,
            Constants::DATE         => date('c'),
            Constants::DURATION     => 0,
            Constants::INTEGRATIONS => []
        ];

        foreach ($this->integrations as $integration) {
            $result[Constants::INTEGRATIONS][] = $this->step($integration, $result);
        }
        $result[Constants::DURATION] = microtime(true) - $begin;
        return $result;
    }
    /**
     * Used to execute handle function of each integration
     *
     * @param array $integration
     * @param array $result
     * @return void
     */
    private function step($integration, &$result)
    {
        $start = microtime(true);
        try {
            $check = $integration[Constants::HANDLE]();
        } catch (\Exception $e) {
            $check = new Check([
                Constants::ERROR => $e
            ]);
        }
        $duration = (microtime(true) - $start);
        if (!$check instanceof Check) {
            $check = new Check([
                Constants::ERROR => Constants::INVALID_CALLBACK_RESPONSE,
                Constants::URL => '',
            ]);
        }
        if (!empty($check->error)) {
            $result[Constants::STATUS] = false;
        }
        return [
            Constants::NAME          => $integration[Constants::NAME],
            Constants::STATUS        => empty($check->error),
            Constants::RESPONSE_TIME => $duration,
            Constants::URL           => $check->url,
            Constants::ERROR         => $check->error instanceof \Exception
                ? $check->error->__toString()
                : $check->error
        ];
    }

    private function validate($data)
    {
        if (!array_key_exists(Constants::INTEGRATIONS, $data)) {
            throw new InvalidArgumentException(Constants::INTEGRATIONS_NOT_PRESENT);
        }
        if (!is_array($data[Constants::INTEGRATIONS])) {
            throw new InvalidArgumentException(Constants::INTEGRATIONS_IS_NOT_A_ARRAY);
        }
        foreach ($data[Constants::INTEGRATIONS] as $integration) {
            if (!is_array($integration)) {
                throw new InvalidArgumentException(Constants::INTEGRATION_ELEMENT_NOT_ARRAY);
            }
            if (!$integration[Constants::HANDLE] instanceof Closure) {
                throw new InvalidArgumentException(Constants::HANDLE_IS_NOT_CLOSURE);
            }
            $this->integrations[] = $integration;
        }
    }
}
