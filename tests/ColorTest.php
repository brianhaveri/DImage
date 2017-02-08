<?php

class ColorTest extends PHPUnit_Framework_TestCase
{
	public function testRgbToHex()
	{
		$tests = [
			'000000' => [0, 0, 0],
			'FF0000' => [255, 0, 0],
			'00FF00' => [0, 255, 0],
			'0000FF' => [0, 0, 255],
			'FFFFFF' => [255, 255, 255],
		];
		foreach($tests as $hex => $rgb) {
			$rgb = array_combine(['r', 'g', 'b'], $rgb);
			$this->assertEquals(strtolower($hex), strtolower(DImage\Color::rgbToHex($rgb['r'], $rgb['g'], $rgb['b'])));
		}
	}

	public function testHexToRgb()
	{
		$tests = [
			'000000'=> [0, 0, 0],
			'FF0000'=> [255, 0, 0],
			'00FF00'=> [0, 255, 0],
			'0000FF'=> [0, 0, 255],
			'FFFFFF'=> [255, 255, 255],
			'000' 	=> [0, 0, 0],
			'F00' 	=> [255, 0, 0],
			'0F0' 	=> [0, 255, 0],
			'00F' 	=> [0, 0, 255],
			'FFF' 	=> [255, 255, 255],
		];
		foreach($tests as $hex => $rgb) {
			$rgb = array_combine(['r', 'g', 'b'], $rgb);
			$this->assertEquals(DImage\Color::hexToRgb($hex), $rgb);
			$this->assertEquals(DImage\Color::hexToRgb('#'.$hex), $rgb);
		}
	}

	public function testColorToHex()
	{
		$tests = array(
			'red'	=> 'FF0000',
			'#f00'	=> '#f00',
		);
		foreach($tests as $color => $hex) {
			$this->assertEquals(DImage\Color::colorToHex($color), $hex);
		}
	}
}
