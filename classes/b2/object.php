<?php

class b2_object
{
	var $id;
	var $b2_instance;
	var $data = array();
	var $attr = array();
	var $___args = array();

	private $__class_file = NULL;

	static $__cache_data = array();

	function arg($name, $def = NULL) { return array_key_exists($name, $this->___args) ? $this->___args[$name] : $def; }

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

	function access()
	{
		$access = $this->access_engine();
		if(!$access)
			bors_throw(ec('Не задан режим доступа к ').$this->object_titled_dp_link());

		return bors_load($access, $this);
	}

	function _access_engine_def() { return NULL; }

	// Не может быть переопределяемой функцией по умолчанию (_auto_objects_def()), так как вызывается в __call()
	function auto_objects() { return array(); }
	// Не может быть переопределяемой функцией по умолчанию (_auto_targets_def()), так как вызывается в __call()
	function auto_targets() { return array(); }

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

//		if($this->strict_auto_fields_check())
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

	function __class_cache_base()
	{
		return config('cache_dir').'/classes/'.str_replace('_', '/', get_class($this));
	}

	function class_cache_data($name = NULL, $setter = NULL)
	{
		if(empty(self::$__cache_data[get_class($this)]))
		{
			if(file_exists($f = $this->__class_cache_base().'.data.json'))
				$data = json_decode(file_get_contents($f), true);

			if(empty($data['class_mtime']) || $data['class_mtime'] != filemtime($this->class_file()))
				self::$__cache_data[get_class($this)] = $data = array();
			else
				self::$__cache_data[get_class($this)] = $data;
		}

		if(!$name)
			return empty(self::$__cache_data[get_class($this)]) ? array() : self::$__cache_data[get_class($this)];

		if(!empty(self::$__cache_data[get_class($this)]) && array_key_exists($name, self::$__cache_data[get_class($this)]))
			return self::$__cache_data[get_class($this)][$name];

		if($setter)
			return $this->set_class_cache_data($name, call_user_func($setter));

		return NULL;
	}

	function _class_title_def()    { return ec('Объект ').@get_class($this); }	// Именительный: Кто? Что?
	function _class_title_dp_def() { return bors_object_titles::class_title_dat($this); }	// Дательный Кому? Чему?

	static function foo()
	{
		return b2::instance()->load(get_called_class(), NULL);
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
				$value = call_user_func(array($this, $name));
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
				return call_user_func($this->attr[$name]);

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
			return $this->attr[$name] = call_user_func(array($this, $m));
		}

		return $default;
	}

	function internal_uri_ascii()
	{
		if(is_object($id = $this->id()))
			$id = $id->internal_uri_ascii();

		if(is_numeric($id))
			$uri = get_class($this).'__'.$id;
		else
			$uri = get_class($this).'__x'.base64_encode($id);

		return $uri;
	}

	function object_titled_dp_link() { return $this->class_title_dp().ec(' «').$this->titled_link().ec('»'); }

	function pre_parse() { return false; }

	function pre_show()
	{
//		if($this->get('objects_visits_counting'))
//			bors_objects_visit::inc($this);

		return false;
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

	function set_attr($attr, $value) { $this->attr[$attr] = $value; return $this; }

	function titled_link()
	{
		$url = $this->url_ex($this->page());
		$title = $this->get('title');

		if(!$title)
			$title = '???';

		return "<a href=\"{$url}\">{$title}</a>";
	}

	function __toString()
	{
		if($tt = $this->get('title_true'))
			return $this->class_title().ec(' «').$tt.ec('»').(is_numeric($this->id()) ? "({$this->id()})" : '');

		return get_class($this).'://'.$this->id().($this->page() > 1 ? ','.$this->page() : '');
	}

	function _url_engine_def() { return 'url_calling2'; }

	function url_ex($args)
	{
		if(is_object($args))
			$page = popval($args, 'page');
		else
		{
			$page = $args;
			$args = array();
		}

		if(!($url_engine = defval($args, 'url_engine')))
			$url_engine = $this->get('url_engine');

		$key = '_url_engine_object_'.$url_engine;

		if(empty($this->attr[$key])/* || !$this->_url_engine->id() ?? */)
			if(!($this->attr[$key] = bors_load($url_engine, $this)))
				bors_throw("Can't load url engine '{$url_engine}' for class {get_class($this)}");

		return $this->attr[$key]->url_ex($page);
	}
}
