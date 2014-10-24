<?php

class b2_log
{
	function error($message)
	{
		$rooms = array(
			array(
				'room_id' => 'Airbase-logs',
				'auth_token' => b2::factory()->conf('hipchat_token', 'b0ZpwNZwsKUVgALbspYmYsqFCiEeDlfpiFBTPqQy'),
			),
		);

		$client = new \Guzzle\Http\Client;
		$hipchat = new \Hipchat\Notifier($client, $rooms, array('pretend' => false));

		// Send the notification.
		$hipchat->notify($message, 'red');
		echo "sent $message\n\n";
	}

	function __dev()
	{
		b2::log()->error("Test message");
	}
}
