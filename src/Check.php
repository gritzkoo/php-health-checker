<?php

namespace K8s\HealthChecker;

class Check
{
    /**
     * Error must have the message to display in response to
     * trace problems with the integration checked
     *
     * @var string|Exception|array
     */
    public $error;
    /**
     * Must have an URL string to help trace the endoint you are testing
     *
     * @var string
     */
    public $url;
    public function __construct(array $data = [])
    {
        $this->error = !empty($data['error']) ? $data['error'] : null;
        $this->url = !empty($data['url']) ? $data['url'] : null;
    }
}
