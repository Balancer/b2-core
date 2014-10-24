<?php

class b2_logger_meta extends \Psr\Log\AbstractLogger
{
	function log($level, $message, array $context = array())
	{
		// Абстрактный метод ничего не делает в отсутствии настроенных логгеров
	}
}
