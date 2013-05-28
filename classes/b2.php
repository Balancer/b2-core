<?php

class b2
{
	private $__config			= [];
	private $__project_classes	= [];
	private $__projects			= [];
	private $composer			= NULL;

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

	private function __load_object($class_name, $id, $args)
	{
		$original_id = $id;
		$object = NULL;

		if($id === 'NULL')
			$id = NULL;

		if(!($class_file = bors_class_loader::file($class_name)))
		{
			if(config('throw_exception_on_class_not_found'))
				return bors_throw("Class '$class_name' not found");

			return $object;
		}

		$found = 0;

		if(method_exists($class_name, 'id_prepare'))
			$id = call_user_func(array($class_name, 'id_prepare'), $id, $class_name);

		// id_prepare нам вернул готовый к использованию объект
		if(is_object($id) && !is_object($original_id))
		{
			$object = $id;
			$id = $object->id();
			$found = 2;
		}
		else
		{
			$object = &load_cached_object($class_name, $id, $args, $found);

			if($object && ($object->id() != $id))
			{
				$found = 0;
				delete_cached_object_by_id($class_name, $id);
				$object = NULL;
			}
		}

		if(!$object)
		{
			$found = 0;
			$object = new $class_name($id);
			if(!method_exists($object, 'set_class_file'))
				return NULL;

			$object->set_class_file($class_file);

			if(config('debug_objects_create_counting_details'))
			{
				bors_function_include('debug/count_inc');
				debug_count_inc("bors_load($class_name,init)");
			}
		}

		$object->_configure();

		$is_loaded = $object->is_loaded();

		if(is_object($is_loaded))
			$object = $is_loaded;

		if(!$is_loaded)
			$is_loaded = $object->data_load();

		if(/*($id || $url) && */!$object->can_be_empty() && !$object->is_loaded())
			return NULL;

		if(!empty($args['need_check_to_public_load']))
		{
			unset($args['need_check_to_public_load']);
			if(!method_exists($object, 'can_public_load') || !$object->can_public_load())
				return NULL;
		}

		if($found != 1 && $object->can_cached())
			save_cached_object($object);

		return $object;
	}

	function load($class_name, $id = NULL)
	{
		if(is_numeric($class_name))
			$class_name = class_id_to_name($class_name);

		if(config('debug_trace_object_load'))
		{
			bors_function_include('debug/hidden_log');
			debug_hidden_log('objects_load', "$class_name($id)", config('debug_trace_object_load_trace'));
		}

		if(!$class_name)
			return;

		if(config('debug_objects_create_counting_details'))
			debug_count_inc("bors_load($class_name)");


		$object = $this->__load_object($class_name, $id, array());

		if(!$object)
			$object = bors_objects_loaders_meta::find($class_name, $id);

		if(!$object)
		{
			if(config('orm.is_strict') && !class_include($class_name))
				bors_throw("Not found class '{$class_name}' for load with id='{$id}'");

			return NULL;
		}

		$object->set_b2($this);
		return $object;
	}

	function projects() { return $this->__projects; }
	function composer() { return $this->composer; }
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

		if(!empty($GLOBALS['composer']))
			$this->composer = $GLOBALS['composer'];
	}
}

function b2_load($class_name, $id = NULL) { return $GLOBALS['b2.instance']->load($class_name, $id); }
