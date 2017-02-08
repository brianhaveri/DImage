<?php

class FactoryTest extends PHPUnit_Framework_TestCase
{
	public function testCreate()
	{
		$image = DImage\Factory::create(
			'foo',
			[
				'text-align' => 'center',
				'vertical-align' => 'middle',
				'width' => 100,
				'height' => 100,
				'background' => DImage\Color::randomHex(),
				'color' => DImage\Color::randomHex(),
				'font-family' => 'ChunkFive',
			],
			__DIR__.'/cache/'.__FUNCTION__.'.jpg'
		);
		$this->assertTrue(is_object($image));
		$this->assertTrue($image instanceof Dimage\Image);
	}
}
