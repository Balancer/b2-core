<?php

if(file_exists($loader=($composer=dirname(dirname(dirname(dirname(__DIR__)))).'/composer').'/vendor/autoload.php'))
	$GLOBALS['bors.composer.class_loader'] = require $loader;
else
	$GLOBALS['bors.composer.class_loader'] = require __DIR__.'/vendor/autoload.php';

require_once $composer.'/vendor/balancer/bors-core/init.php';
