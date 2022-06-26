# PHP Health Checker

[![test](https://github.com/gritzkoo/php-health-checker/actions/workflows/test.yml/badge.svg)](https://github.com/gritzkoo/php-health-checker/actions/workflows/test.yml)
[![Coverage Status](https://coveralls.io/repos/github/gritzkoo/php-health-checker/badge.svg?branch=main)](https://coveralls.io/github/gritzkoo/php-health-checker?branch=main)
![GitHub issues](https://img.shields.io/github/issues/gritzkoo/php-health-checker)
![GitHub pull requests](https://img.shields.io/github/issues-pr/gritzkoo/php-health-checker)
![GitHub](https://img.shields.io/github/license/gritzkoo/php-health-checker)
![GitHub repo size](https://img.shields.io/github/repo-size/gritzkoo/php-health-checker)
![Packagist Downloads](https://img.shields.io/packagist/dt/gritzkoo/php-health-checker)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/gritzkoo/php-health-checker)
![Packagist Stars](https://img.shields.io/packagist/stars/gritzkoo/php-health-checker)
![GitHub language count](https://img.shields.io/github/languages/count/gritzkoo/php-health-checker)
___

This is a PHP package that allows you to track the health of your application, providing two **_methods_** of checking `$checkt->liveness()` and `$checker->readiness()`:

## How to install

```sh
composer require gritzkoo/php-health-checker
```

## Bootstrapping the checker

```php
<?php

require 'vendor/autoload.php';

use Gritzkoo\HealthChecker\Check;
use Gritzkoo\HealthChecker\HealthChecker;

$checker = new HealthChecker([
    // optional prop, will be used to readiness response
    'name' => 'My application name', 
    // version is used in liveness and readiness actions
    'version' => 'v1.0.0', // more details in version section of this document!
    // the list of checks you whant to test, is just an array of array with name and handle function
    'integrations' => [
        [
            // the name of the integration you are trying to verify
            'name' => 'github status check',
            // here is just an example of how to make your own check
            // you can inject this function the way you want, you only need to return
            // a instance of Gritzkoo\HealthChecker\Check
            // The HealthCheker will interpret your check fails when the $check->error is not empty
            'handle' => function () {
                $check = new Check([
                    'url' => 'https://github.com/status'
                ]);
                $ch = curl_init($check->url);
                try {
                    $response = curl_exec($ch);
                } catch (Exception $e) {
                    $check->error = $e;
                }
                $info = curl_getinfo($ch);
                curl_close($ch);
                if ($info['http_code'] != 200) {
                    $check->error = [
                        'response' => $response,
                        'info' => $info
                    ];
                }
                return $check;
            }
        ]
    ]
]);

```

## Example of use

You can view this full Laravel application, with this package installed in:

[https://github.com/gritzkoo/php-health-checker-example-app](https://github.com/gritzkoo/php-health-checker-example-app)

___

## $checker->liveness()

Will return an **_ARRAY_** that you can convert to **_JSON_** as below and that allows you to check if your application is _OK_ without checking any kind of integration.

```json
{
    "status": "fully functional", 
    "version": "v1.0.0"
}
```

___

## $checker->readiness()

Will return an **_ARRAY_** that you can convert to **_JSON_** as below and that allows you to check if your application is up and running and check if all of your integrations informed in the configuration list are up and running.

```json
{
    "name": "My application name",
    "version": "v1.0.0",
    // the main status checks, will return true when all integrations does not fail
    "status": true, 
    // ISO 8601 date
    "date": "2022-06-25T11:52:56-03:00",
    "duration": 0.08681011199951172,
    "integrations": [
        {
            "name": "github status check",
            "status": true,
            "response_time": 0.08406686782836914,
            "url": "https://github.com/status",
            "error": null
        }
    ]
}
```


## Create a HTTP inteface to expose probs

>Using Laravel application example <https://github.com/gritzkoo/php-health-checker-example-app>

Once you create an instance of `Gritzkoo\HealthChecker\HealthChecker` you should create 2 routes in your application to expose `liveness` and `readiness` actions like:

- Controller <https://github.com/gritzkoo/php-health-checker-example-app/blob/main/app/Http/Controllers/HealthCheckController.php#L10-L22>
- Route <https://github.com/gritzkoo/php-health-checker-example-app/blob/main/routes/web.php#L20-L22>


And then, you could call these endpoints manually to see your application health, but, if you are using modern Kubernetes deployment, you can config your chart to check your application with the setup below:

```yaml
apiVersion: v1
kind: Pod
metadata:
  labels:
    test: liveness
  name: liveness-http
spec:
  containers:
  - name: liveness
    image: 'node' #your application image
    args:
    - /server
    livenessProbe:
      httpGet:
        path: /health-check/liveness
        port: 80
        httpHeaders:
        - name: Custom-Header
          value: Awesome
      initialDelaySeconds: 3
      periodSeconds: 3
  - name: readiness
    image: 'node' #your application image
    args:
    - /server
    readinessProbe:
      httpGet:
        path: /health-check/readiness
        port: 80
        httpHeaders:
        - name: Custom-Header
          value: Awesome
      initialDelaySeconds: 3
      periodSeconds: 3
```
