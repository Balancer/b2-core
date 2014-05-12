<?php

if(file_exists($loader=($composer=dirname(dirname(dirname(dirname(__DIR__)))).'/composer').'/vendor/autoload.php'))
	$GLOBALS['bors.composer.class_loader'] = require $loader;
elseif(file_exists($loader=($composer=dirname(__DIR__).'/composer').'/vendor/autoload.php'))
	$GLOBALS['bors.composer.class_loader'] = require $loader;
else
	$GLOBALS['bors.composer.class_loader'] = require __DIR__.'/vendor/autoload.php';

if(file_exists($bors_core = $composer.'/vendor/balancer/bors-core/init.php'))
	require_once $bors_core;
else
	require_once BORS_CORE.'/init.php';

