<?php

class b2_page extends bors_page
{
	static function factory()
	{
		return b2::factory()->load(get_called_class(), NULL);
	}

	var $template = 'xfile:bootstrap3-noassets.tpl';
}
