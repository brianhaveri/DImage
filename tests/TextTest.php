<?php

class TextTest extends PHPUnit_Framework_TestCase
{
	public function testSanitize()
	{
		$this->assertEquals("Foo's Bar", DImage\Text::sanitize("Foo’s Bar"));
	}

	public function testTransform()
	{
		$this->assertEquals('FOOBAR', Dimage\Text::transform('foobar', 'uppercase'));
		$this->assertEquals('foobar', Dimage\Text::transform('FOOBAR', 'lowercase'));
		$this->assertEquals('Foo Bar', Dimage\Text::transform('foo bar', 'capitalize'));
	}
}
