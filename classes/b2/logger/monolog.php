<?php

// composer require monolog/monolog=*

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class b2_logger_monolog extends b2_logger_meta
{
	function __construct()
	{
		// create a log channel
		$log = new Logger('B2');
		$log->pushHandler(new StreamHandler(COMPOSER_ROOT.'/test.log', Logger::WARNING));

		if($tokens = b2::instance()->conf('hipchat.room_tokens'))
			foreach($tokens as $room => $token)
			{
				echo $room, PHP_EOL;
//     public function __construct($token, $room, $name = 'Monolog', $notify = false, $level = Logger::CRITICAL, $bubble = true, $useSSL = true, $format = 'text')

				$log->pushHandler(new \Monolog\Handler\HipChatHandler($token, $room, 'BORS©v2', false, Logger::ERROR));
			}

// public function __construct($level = Logger::DEBUG, array $skipClassesPartials = array('Monolog\\'))
		$log->pushProcessor(new \Monolog\Processor\IntrospectionProcessor(Logger::DEBUG, array("Monolog\\", 'b2_logger_monolog')));
		$log->pushProcessor(new \Monolog\Processor\WebProcessor());

		$this->log = $log;
	}

	function debug($message)	{ return $this->log->addDebug($message); }
	function info($message)		{ return $this->log->addInfo($message); }
	function notice($message)	{ return $this->log->addNotice($message); }
	function warning($message)	{ return $this->log->addWarning($message); }
	function error($message)	{ return $this->log->addError($message); }
	function critical($message)	{ return $this->log->addCritical($message); }
	function alert($message)	{ return $this->log->addAlert($message); }
	function emergency($message){ return $this->log->addEmergency($message); }
}
