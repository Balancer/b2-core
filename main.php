<?php

$GLOBALS['b2.stat']['start_microtime'] = microtime(true);

// Инициализация фреймворка
require_once(__DIR__.'/init.php');

$uri = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

$b2 = new b2;
$b2->init();
$result = NULL;

	if($b2->conf('debug.execute_trace'))
		debug_execute_trace("\$b2->load_uri('$uri');");

	if($object = $b2->load_uri($uri))
	{
		// Если это редирект
		if(!is_object($object))
			return $b2->go($object);

		$result = $object->show();
	}

// Если объект всё, что нужно нарисовал сам, то больше нам делать нечего. Выход.
if($result === true)
	return;

// Если объект вернул строку, то рисуем её и выходим.
if($result)
{
	echo $result;
	return;
}

@header("HTTP/1.0 404 Not Found");
