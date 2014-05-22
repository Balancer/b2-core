<?php

// http://stackoverflow.com/questions/6079492/php-how-to-print-a-debug-log

class b2d // bors2-debug
{
	static function d($var)
	{
//		ob_start();
//		// https://github.com/raveren/kint
//		// http://raveren.github.io/kint/#advanced
//		Kint::dump($var);
//		$s = ob_get_contents();
//		ob_end_clean();
		// http://phpdebugbar.com/docs/rendering.html
		// https://github.com/maximebf/php-debugbar
		$GLOBALS['debugbar']['messages']->addMessage(var_export($var, true), 'debug', false);
	}
}
