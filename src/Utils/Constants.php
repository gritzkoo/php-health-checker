<?php

namespace Gritzkoo\HealthChecker\Utils;

class Constants
{
    // METHOD NAMES ===============================================================================
    const LIVENESS = "liveness";
    const READINESS = "readiness";
    // ============================================================================================
    // DEFAULT ARRAY KEYS =========================================================================
    const DATE = "date";
    const DURATION = "duration";
    const ERROR = "error";
    const HANDLE = "handle";
    const INTEGRATION = "integration";
    const INTEGRATIONS = "integrations";
    const NAME = "name";
    const RESPONSE_TIME = "response_time";
    const STATUS = "status";
    const URL = "url";
    const VERSION = "version";
    // ============================================================================================
    // MESSAGES ===================================================================================
    const FULLY_FUNCTIONAL = "fully functional";
    const DATA_IS_NOT_A_ARRAY = 'Argument #1 ($data) must be of type array, null given';
    const INTEGRATIONS_NOT_PRESENT = "The integrations is not present.";
    const INTEGRATIONS_IS_NOT_A_ARRAY = "The integrations key is not a array";
    const INTEGRATION_ELEMENT_NOT_ARRAY = "Element of integrations must be an array";
    const HANDLE_IS_NOT_CLOSURE = "The handle key must be a Closure function";
    const INVALID_CALLBACK_RESPONSE = "Integration failed because return not a instance of Check";
    // ============================================================================================
}
