<?php

// composer require rollbar/rollbar=*

class b2_logger_rollbar extends b2_logger_meta
{
	function __construct()
	{
		$config = array(
		    // required
    		'access_token' => b2::instance()->conf('rollbar.token'),
		    // optional - environment name. any string will do.
		    // 'environment' => 'production',
		    // optional - path to directory your code is in. used for linking stack traces.
		 	// 'root' => '/Users/brian/www/myapp'
		);

		Rollbar::init($config);
	}

	function log($level, $message, array $context = array())
	{
//		Rollbar::report_exception($e);
		Rollbar::report_message($message, $level, $context);
	}
}
