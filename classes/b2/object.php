<?php

class b2_object
{
	var $id;
	var $b2_instance;
	var $data = array();
	var $attr = array();

	private $__class_file = NULL;

	static function id_prepare($id) { return $id; }

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

	// Не может быть переопределяемой функцией по умолчанию (_auto_objects_def()), так как вызывается в __call()
	function auto_objects() { return array(); }
	// Не может быть переопределяемой функцией по умолчанию (_auto_targets_def()), так как вызывается в __call()
	function auto_targets() { return array(); }

	function __call($method, $params)
	{
		// Ели это был вызов $obj->set_XXX($value, $db_up)
		if(preg_match('!^set_(\w+)$!', $method, $match))
			return $this->set($match[1], $params[0], array_key_exists(1, $params) ? $params[1] : true);

		// Проверяем нет ли уже загруженного значения атрибута (временных несохраняемых данных) объекта
		// Приоритет атрибута выше, чем приоритет параметров, так как в атрибутах
		// может лежать изменённое значение параметра
		// Если это где-то что-то поломает — исправить там, а не тут.
		if(array_key_exists($method, $this->attr))
		{
			// Если хранимый атрибут — функция, то вызываем её, передав параметр.
			if(is_callable($this->attr[$method]))
				return call_user_func_array($this->attr[$method], $params);

			// Иначе — просто возвращаем значение.
			return $this->attr[$method];
		}

		// Проверяем нет ли уже загруженного значения данных объекта
		if(@array_key_exists($method, $this->data))
			return $this->data[$method];

		// Проверяем нет ли уже загруженного значения автообъекта
		if(@array_key_exists($method, $this->__auto_objects))
		{
			$x = $this->__auto_objects[$method];
			if($x['property_value'] == $this->get($x['property']))
				return $x['value'];
		}

		// Проверяем автоматические объекты.
		$auto_objs = $this->auto_objects();
		if(($f = @$auto_objs[$method]))
		{
			if(preg_match('/^(\w+)\((\w+)\)$/', $f, $m))
			{
				$property = $m[2];
				if(config('orm.auto.cache_attr_skip'))
					return b2::load($m[1], $this->get($property));
				else
				{
					$property_value = $this->get($property);
					$value = b2::load($m[1], $property_value);
					$this->__auto_objects[$method] = compact('property', 'property_value', 'value');
					return $value;
				}
			}
		}

		// Автоматические целевые объекты (имя класса задаётся)
		$auto_targs = $this->auto_targets();
		if(($f = @$auto_targs[$method]))
			if(preg_match('/^(\w+)\((\w+)\)$/', $f, $m))
				if(config('orm.auto.cache_attr_skip'))
					return object_load($this->$m[1](), $this->$m[2]());
				else
					return $this->attr[$method] = object_load($this->$m[1](), $this->$m[2]());

		$name = $method;

		// Проверяем одноимённые переменные (var $title = 'Files')
		if(property_exists($this, $name))
			return $this->set_attr($name, $this->$name);

		// Ищем методы, перекрываемые переменным по умолчанию
		$m = "_{$name}_def";
		if(method_exists($this, $m))
		{
			// Try убран, так как нужно решить, как обрабатывать всякие function _title_def() { bors_throw('Заголовок не указан!';} — см. bors_rss
			$value = $this->$m();
//			var_dump($m, $value);
//			try { $value = $this->$m(); }
//			catch(Exception $e) { $value = NULL; }
			return $this->attr[$name] = $value;
		}

		if(bors_lib_orm::get_yaml_notation($this, $name))
			return $this->attr[$name];

		if($this->strict_auto_fields_check())
		{
			$trace = debug_backtrace();
			$trace = array_shift($trace);
			bors_throw("__call[".__LINE__."]:
undefined method '$method' for class '<b>".get_class($this)."({$this->id()})</b>'<br/>
defined at {$this->class_file()}<br/>
". (!empty($trace['file']) ? "called from {$trace['file']}:{$trace['line']}" : ''));
		}

		return NULL;
	}

	function set($prop, $value)
	{
		if(!is_array($value)
				&& !is_object($value)
				&& strcmp(@$this->data[$prop], $value)
			) // TODO: если без контроля типов, то !=, иначе - !==
		{
			if(config('mutex_lock_enable'))
				$this->__mutex_lock();

			//TODO: продумать систему контроля типов.
			//FIXME: чёрт, тут нельзя вызывать всяких user, пока в них лезут ошибки типов. Исправить и проверить все основные проекты.
//			if(@$this->data[$prop] == $value && @$this->data[$prop] !== NULL && $value !== NULL)
//				debug_hidden_log('types', 'type_mismatch: value='.$value.'; original type: '.gettype(@$this->data[$prop]).'; new type: '.gettype($value));

			// Запоминаем первоначальное значение переменной.
			if(!@array_key_exists($prop, $this->changed_fields))
				$this->changed_fields[$prop] = @$this->data[$prop];

			bors()->add_changed_object($this);
		}

		$this->attr[$prop] = $value; // У атрибутов выше приоритет. Так что их тоже надо менять. Ну а данные — они на запись.
		$this->data[$prop] = $value;
		return $this;
	}

	function get($name, $default = NULL)
	{
		if(!$name)
			return NULL;

		if(method_exists($this, $name))
		{
			$value = NULL;
			try
			{
				$value = call_user_func_array(array($this, $name), $params);
			}
			catch(Exception $e)
			{
				bors_debug::syslog('get-exception', "Exception ".$e->getMessage()." while get ".get_class($this)."->name()");
				$value = NULL;
			}

			return $value;
		}

		// У атрибутов приоритет выше, так как они могут перекрывать data.
		// Смотри также в __call
		if(array_key_exists($name, $this->attr))
		{
			// Если хранимый атрибут — функция, то вызываем её, передав параметр.
			if(is_callable($this->attr[$name]))
				return call_user_func_array($this->attr[$name], $params);

			// Иначе — просто возвращаем значение.
			return $this->attr[$name];
		}

		if(@array_key_exists($name, $this->data))
			return $this->data[$name];

		if($name == 'this')
			return $this;

		// Проверяем параметры присоединённых объектов
		if(!empty($this->_prop_joins))
			foreach($this->_prop_joins as $x)
				if(array_key_exists($name, $x->data))
					return $this->attr[$name] = $x->data[$name];

		// Проверяем автоматические объекты.
		$auto_objs = $this->auto_objects();
		if(!empty($auto_objs[$name]))
		{
			if(preg_match('/^(\w+)\((\w+)\)$/', $auto_objs[$name], $m))
			{
				try { $value = bors_load($m[1], $this->get($m[2])); }
				catch(Exception $e) { $value = NULL; }
				return $this->attr[$name] = $value;
			}
		}

		// Автоматические целевые объекты (имя класса задаётся)
		$auto_targs = $this->auto_targets();
		if(!empty($auto_targs[$name]))
		{
			if(preg_match('/^(\w+)\((\w+)\)$/', $auto_targs[$name], $m))
				return $this->attr[$name] = bors_load($this->get($m[1]), $this->get($m[2]));
		}

		// Проверяем одноимённые переменные (var $title = 'Files')
		if(property_exists($this, $name))
			return $this->set_attr($name, $this->$name);

		// Ищем методы, перекрываемые переменным по умолчанию
		$m = "_{$name}_def";
		if(method_exists($this, $m))
		{
			// Try убран, так как нужно решить, как обрабатывать всякие function _title_def() { bors_throw('Заголовок не указан!';} — см. bors_rss
			return $this->attr[$name] = call_user_func_array(array($this, $m), $params);
		}

		return $default;
	}

	function attr($name, $default = NULL)
	{
		if(array_key_exists($name, $this->attr))
		{
			// Если хранимый атрибут — функция, то вызываем её, передав параметр.
			if(is_callable($this->attr[$name]))
				return call_user_func($this->attr[$name]);

			// Иначе — просто возвращаем значение.
			return $this->attr[$name];
		}

		return $default;
	}

	function set_attr($attr, $value) { $this->attr[$attr] = $value; return $this; }

	static function foo()
	{
		return b2::factory()->load(get_called_class(), NULL);
	}
}
