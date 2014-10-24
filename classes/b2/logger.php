<?php

class b2_logger extends \Psr\Log\AbstractLogger
{
	var $loggers = array();

	function __construct()
	{
		$b2 = b2::instance();
		$b2->init();
//		var_dump($b2->conf('logger.classes', 'b2_logger_meta'));
//		$this->loggers[] = new b2_logger_hipchat;
//		$this->loggers[] = new b2_logger_monolog;
		$this->loggers[] = new b2_logger_rollbar;
	}

	function log($level, $message, array $context = array())
	{
		var_dump($level);
		if(empty($context['trace']))
			$context['trace'] = debug_backtrace();

		foreach($this->loggers as $log)
			$log->log($level, $message, $context);
	}

	function __dev()
	{
		b2::instance()->log()->error("Test message from b2_logger");
	}
}
