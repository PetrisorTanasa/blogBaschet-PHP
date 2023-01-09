<?php

use App\Kernel;

if(isset($_SERVER["HTTP_REFERER"]) and ($_SERVER["HTTP_HOST"] != "baschet-bucurestean.herokuapp.com" and $_SERVER["HTTP_HOST"] != "127.0.0.1:8000")){
    echo $_SERVER["HTTP_HOST"];
    die();
}
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

$trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false;
$trustedProxies = $trustedProxies ? explode(',', $trustedProxies) : [];
//if($_SERVER['APP_ENV'] == 'prod') $trustedProxies[] = $_SERVER['REMOTE_ADDR'];
if($trustedProxies) {
    Request::setTrustedProxies($trustedProxies, Request::HEADER_X_FORWARDED_AWS_ELB);
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
