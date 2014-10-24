<?php

class b2_logger_meta
{
	function message($message, $color)
	{
		// Ничего не делаем, если не подключены остальные логгеры.
	}

	// Detailed debug information.
	function debug($message) { return $this->message('DEBUG: '.$message, 'gray'); }

	// Interesting events. Examples: User logs in, SQL logs.
	function info($message) { return $this->message('INFO: '.$message, 'green'); }

	// Normal but significant events.
	function notice($message) { return $this->message('NOTICE: '.$message, 'yellow'); }

	// Exceptional occurrences that are not errors. Examples: Use of deprecated APIs,
	// poor use of an API, undesirable things that are not necessarily wrong.
	function warning($message) { return $this->message('WARNING: '.$message, 'yellow'); }

	// Runtime errors that do not require immediate action but should typically be logged and monitored.
	function error($message) { return $this->message('ERROR: '.$message, 'red'); }

	// Critical conditions. Example: Application component unavailable, unexpected exception.
	function critical($message) { return $this->message('CRITICAL: '.$message, 'red'); }

	// Action must be taken immediately. Example: Entire website down,
	// database unavailable, etc. This should trigger the SMS alerts and wake you up.
	function alert($message) { return $this->message('ALERT: '.$message, 'red'); }

	// Emergency: system is unusable.
	function emergency($message) { return $this->message('EMERGENCY: '.$message, 'red'); }
}
