<?php

$request_file = $_SERVER['DOCUMENT_ROOT'].'/'.$_SERVER['REQUEST_URI'];
if(file_exists($request_file) && !is_dir($request_file))
	return false;

$GLOBALS['b2.stat']['start_microtime'] = microtime(true);

// Инициализация фреймворка
require_once(__DIR__.'/init.php');

$uri = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

$result = NULL;

$b2 = b2::factory();

	if($b2->conf('debug.execute_trace'))
		debug_execute_trace("\$b2->load_uri('$uri');");

	if($object = $b2->load_uri($uri))
	{
		// Если это редирект
		if(!is_object($object))
			return $b2->go($object);

		$result = $object->direct_content();
	}

// Если объект всё, что нужно нарисовал сам, то больше нам делать нечего. Выход.
if($result === true)
	return;

// Если объект вернул строку, то рисуем её и выходим.
if($result)
{
	echo $result;
	return true;
}

@header("HTTP/1.0 404 Not Found");
echo "Not found: <b>$uri</b>";
return true;
