<?php
declare(strict_types=1);

namespace think;

define('APP_PATH', __DIR__ . '/../app/');
define('ROOT_PATH', __DIR__ . '/../');

require_once __DIR__ . '/../vendor/autoload.php';

$app = new App();

$http = $app->http;

$response = $http->run();

$response->send();

$http->end($response);
