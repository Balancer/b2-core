<?php

class b2_object_unittest extends PHPUnit_Framework_TestCase
{
	public function test_b2_object()
	{
		$object = b2_object::foo();
		$this->assertNotNull($object);

		// Проверяем работу с атрибутами
		$object->set_attr('book_author', 'Лев Толстой')
			->set_attr('book_title', 'Война и мир');

		// Теперь смотрим, как оно сохранилось
		$this->assertEquals('Лев Толстой', $object->book_author());
		$this->assertEquals('Лев Толстой', $object->attr('book_author'));
		$this->assertEquals('Лев Толстой', $object->get('book_author'));
		$this->assertEquals('Война и мир', $object->attr('book_title'));
		$this->assertEquals('Война и мир', $object->book_title());
		$this->assertEquals('Война и мир', $object->get('book_title'));

		// Отсутствующие поля
		$this->assertNull($object->get('favorite_color'));
		// Значение по умолчанию
		$this->assertEquals('red', $object->get('favorite_color', 'red'));
		// Убедимся, что не запомнилось
		$this->assertNull($object->get('favorite_color'));

		// Проверка callable-атрибутов. Может использоваться для внедрения методов.
		$object->set_attr('square', function($x) { return $x*$x;} );
		$this->assertEquals(4, $object->square(2));
	}
}
