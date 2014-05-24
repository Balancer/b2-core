<?php

require_once(__DIR__.'/vendor/autoload.php');

if(!defined('BORS_CORE'))
{
	define('BORS_SITE', __DIR__);
	bors::init();
}

$host = config('webserver.host', '0.0.0.0');
$port = config('webserver.port', '8000');

echo "Run php-webserver at http://$host:$port/", PHP_EOL;
chdir('htdocs/');
system("php -S $host:$port ".__DIR__."/main.php");
