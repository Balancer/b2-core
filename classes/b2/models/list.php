<?php

class b2_models_list extends b2_model
{
	private static $objects = NULL;

	function __construct($id)
	{
		// Если объекты списка не загружали, то загружаем.
		if(is_null($this->objects))
		{
			foreach($this->objects_list() as $id => $data)
			{
				if(!is_array($data))
					$data = array('title' => $data);

				
			}
		}

		parent::__construct($id);
	}
}
