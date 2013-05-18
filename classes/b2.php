<?php

class b2
{
	var $__config			= [];
	var $__project_classes	= [];
	var $__projects			= [];

	// Вернуть значение параметра конфигурации
	function conf($name, $default = NULL)
	{
		return array_key_exists($name, $this->__config) ? $this->__config[$name] : $default;
	}

	// Загрузить объект по соответствующему URI роутера
	function load_uri($uri)
	{
		foreach($this->projects() as $project)
		{
			// У каждого проекта — собственный роутер
			if($obj = $project->router()->load_uri($uri))
				return $obj;
		}

		return NULL;
	}

	function load($class_name, $id = NULL)
	{
		$object = new $class_name;
		$object->set_id($id);
		$object->set_b2($this);
		return $object;
	}

	function projects() { return $this->__projects; }
	function init()
	{
		$GLOBALS['b2.instance'] = $this;

		$this->__project_classes[] = 'b2f_project';
		foreach($this->__project_classes as $project_class)
		{
			$project = $this->load($project_class);
			$project->set_b2($this);
			$this->__projects[] = $project;
		}
	}
}

function b2_load($class_name, $id = NULL) { return $GLOBALS['b2.instance']->load($class_name, $id); }
