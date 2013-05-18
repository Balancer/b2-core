<?php

class b2_object
{
	var $id;
	var $b2_instance;

	function set_id($id) { $this->id = $id; return $this; }

	function b2() { return $this->b2_instance; }
	function set_b2($b2_instance) { $this->b2_instance = $b2_instance; return $this; }
}
