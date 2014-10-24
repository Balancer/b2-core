<?php

class b2_logger
{
	var $loggers = array();

	function __construct()
	{
		$b2 = b2::instance();
		$b2->init();
//		var_dump($b2->conf('logger.classes', 'b2_logger_meta'));
		$this->loggers[] = new b2_logger_hipchat;
	}

	function message($type, $message, $color)
	{
		foreach($this->loggers as $log)
			call_user_func(array($log, $type), $message, $color);
	}

	// Detailed debug information.
	function debug($message) { return $this->message('debug', $message, 'gray'); }

	// Interesting events. Examples: User logs in, SQL logs.
	function info($message) { return $this->message('info', $message, 'green'); }

	// Normal but significant events.
	function notice($message) { return $this->message('notice', $message, 'yellow'); }

	// Exceptional occurrences that are not errors. Examples: Use of deprecated APIs,
	// poor use of an API, undesirable things that are not necessarily wrong.
	function warning($message) { return $this->message('warning', $message, 'yellow'); }

	// Runtime errors that do not require immediate action but should typically be logged and monitored.
	function error($message) { return $this->message('error', $message, 'red'); }

	// Critical conditions. Example: Application component unavailable, unexpected exception.
	function critical($message) { return $this->message('critical', $message, 'red'); }

	// Action must be taken immediately. Example: Entire website down,
	// database unavailable, etc. This should trigger the SMS alerts and wake you up.
	function alert($message) { return $this->message('alert', $message, 'red'); }

	// Emergency: system is unusable.
	function emergency($message) { return $this->message('emergency', $message, 'red'); }

	function __dev()
	{
		b2::instance()->log()->error("Test message");
	}
}
