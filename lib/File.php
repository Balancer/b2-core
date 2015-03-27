<?php

namespace B2;

class File
{
	/**
	 * Многопоточно-безопасная запись файла. Гарантируется атомарность файла.
	 * @param 	string	$file		имя файла
	 * @param	string	$content	записываемое содержимое
	 * @param	int		$mode		права доступа к файлу
	*/

	static function put($file, $content, $mode = 0664)
	{
	    if(!$fh = fopen($file, 'a+'))
			bors_throw("Can't open write {$file}");

	    if(!flock($fh, LOCK_EX))
			bors_throw("Can't lock write {$file}");

	    if(!ftruncate($fh, 0))
			bors_throw("Can't truncate write {$file}");

	    fwrite($fh, $content);
    	fclose($fh);

		chmod($file, $mode);
	}
}
