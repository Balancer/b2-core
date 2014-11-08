<?php

class b2_models_db extends b2_object
{
	function new_instance() { bors_object_new_instance_db($this); }

	function _project_name_def() { return bors_core_object_defaults::project_name($this); }

	function table_name()
	{
		if(!empty($this->attr['table_name']))
			return $this->attr['table_name'];

		if(!empty($this->data['table_name']))
			return $this->data['table_name'];

		if(!empty($this->table_name))
			return $this->table_name;

		if($tab = $this->get('table_name', NULL, true))
			return $tab;

		$class_name = str_replace('_admin_', '_', $this->class_name());
		if(preg_match('/^'.preg_quote($this->project_name(),'/').'_(\w+)$/i', $class_name, $m))
			return $this->attr['table_name'] = bors_plural(blib_grammar::chunk_singular($m[1]));

		return $this->attr['table_name'] = $this->_item_name_m();
	}
}
