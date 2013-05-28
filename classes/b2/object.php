<?php

class b2_object
{
	var $id;
	var $b2_instance;
	private $__class_file = NULL;

	function set_id($id) { $this->id = $id; return $this; }

	function b2() { return $this->b2_instance; }
	function set_b2($b2_instance) { $this->b2_instance = $b2_instance; return $this; }

	function class_file() { return $this->__class_file; }
	function set_class_file($class_file) { $this->__class_file = $class_file; return $this; }

	function configure() { }
	function _configure() { }
	function is_loaded() { return true; }
	function can_be_empty() { return true; }
	function can_cached() { return true; }
}
