# PHP Health Checker

[![test](https://github.com/gritzkoo/php-health-checker/actions/workflows/test.yml/badge.svg)](https://github.com/gritzkoo/php-health-checker/actions/workflows/test.yml)
[![Coverage Status](https://coveralls.io/repos/github/gritzkoo/php-health-checker/badge.svg?branch=main)](https://coveralls.io/github/gritzkoo/php-health-checker?branch=main)

___

This is a PHP package that allows you to track the health of your application, providing two **_methods_** of checking `$checkt->liveness()` and `$checker->readiness()`:

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

You will find a more detailed form to create an instance of `Gritzkoo\HealthChecker\HealthChecker` [HERE](./docs/examples/using-poo.php)

___

## $checker->liveness()

Will return an **_ARRAY_** that you can convert to **_JSON_** as below and that allows you to check if your application is *OK* without checking any kind of integration.

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
    // 
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

## How to install

```sh
composer require gritzkoo/php-health-checker
```
## Create a HTTP inteface to expose probs

Once you create an instance of `Gritzkoo\HealthChecker\HealthChecker` you should create 2 routes in your application to expose `liveness` and `readiness` actions like:

> example using laravel route approach

____

>controller using a example of [Checker Instance like](./docs/examples/using-poo.php)

```php
<?php

namespace App\Http\Controllers;

use App\Services\Healthcheck;

class HealthCheckController extends Controller
{
    private $check;
    public function __construct(Healthcheck $checker)
    {
        $this->checker = $checker;
    }
    public function liveness()
    {
        return $this->checker->liveness()
    }
    public function readiness()
    {
        return $this->checker->readiness()
    }
}
``` 

___

>route file `routes/web.php` 
```php
<?php

use App\Http\Controllers\HealthCheckController;

Route::get('/health-check/liveness', [HealthCheckController::class, 'liveness'])->name('liveness');
Route::get('/health-check/readiness', [HealthCheckController::class, 'readiness'])->name('readiness');
```

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
