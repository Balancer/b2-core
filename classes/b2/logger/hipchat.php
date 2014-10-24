<?php

// composer require hannesvdvreken/hipchat=*

class b2_logger_hipchat extends b2_logger_meta
{
	function message($message, $color)
	{
		$rooms = array();

		foreach(b2::instance()->conf('hipchat.room_tokens') as $room => $token)
		{
			$rooms[] = array(
				'room_id' => $room,
				'auth_token' => $token,
			);
		}

		$client = new \Guzzle\Http\Client;
		$hipchat = new \Hipchat\Notifier($client, $rooms, array('pretend' => false));

		// Send the notification.
		$hipchat->notify($message, $color);
	}
}
