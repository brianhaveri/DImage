<?php

class StyleTest extends PHPUnit_Framework_TestCase
{
	public function testPositionToPercent()
	{
		$positions = [
			'left'	=> '0%',
			'right'	=> '100%',
			'top'	=> '0%',
			'bottom'=> '100%',
			'center'=> '50%',
		];
		foreach ($positions as $position => $percent) {
			$this->assertEquals($percent, DImage\Style::positionToPercent($position));
		}
	}

	public function testFillStyleBackground()
	{
		$backgrounds = array(
			'url("/image/foo.jpg")' => [
				'background-color'		=> 'transparent',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'repeat',
				'background-position-x'	=> 0,
				'background-position-y'	=> 0,
			],
			'url(/image/foo.jpg?v=13) no-repeat' => [
				'background-color'		=> 'transparent',
				'background-image'		=> '/image/foo.jpg?v=13',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> 0,
				'background-position-y'	=> 0,
			],
			'transparent url("/image/fooBar.jpg") repeat 0 0' => [
				'background-color'		=> 'transparent',
				'background-image'		=> '/image/fooBar.jpg',
				'background-repeat'		=> 'repeat',
				'background-position-x'	=> 0,
				'background-position-y'	=> 0,
			],
			'transparent url(\'/image/foo.jpg\') no-repeat 0px 10%' => [
				'background-color'		=> 'transparent',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '0px',
				'background-position-y'	=> '10%',
			],
			'transparent url("/image/foo.jpg") no-repeat 10px 10px' => [
				'background-color'		=> 'transparent',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '10px',
				'background-position-y'	=> '10px',
			],
			'#f00 url("/image/foo.jpg") no-repeat 0 0' => [
				'background-color'		=> '#f00',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> 0,
				'background-position-y'	=> 0,
			],
			'url("/image/foo bar.jpg") no-repeat 0 0' => [
				'background-color'		=> 'transparent',
				'background-image'		=> '/image/foo bar.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> 0,
				'background-position-y'	=> 0,
			],
			'url("/image/foo.jpg") repeat-y 0 0' => [
				'background-color'		=> 'transparent',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'repeat-y',
				'background-position-x'	=> 0,
				'background-position-y'	=> 0,
			],
			'url("/image/foo.jpg") #f00' => [
				'background-color'		=> '#f00',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'repeat',
				'background-position-x'	=> 0,
				'background-position-y'	=> 0,
			],
			'url("/image/foo.jpg") no-repeat' => [
				'background-color'		=> 'transparent',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> 0,
				'background-position-y'	=> 0,
			],
			'url("/image/foo.jpg") red no-repeat' => [
				'background-color'		=> '#FF0000',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> 0,
				'background-position-y'	=> 0,
			],
			'url("/image/foo.jpg") no-repeat #09f 0px 90%' => [
				'background-color'		=> '#09f',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '0px',
				'background-position-y'	=> '90%',
			],
			'url("/image/foo.jpg") no-repeat #09f -20px -90%' => [
				'background-color'		=> '#09f',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '-20px',
				'background-position-y'	=> '-90%',
			],
			'url("/image/foo.jpg") no-repeat #09f right top' => [
				'background-color'		=> '#09f',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '100%',
				'background-position-y'	=> '0%',
			],
			'url("/image/foo.jpg") no-repeat #09f left bottom' => [
				'background-color'		=> '#09f',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '0%',
				'background-position-y'	=> '100%',
			],
			'url("/image/foo.jpg") no-repeat #09f center' => [
				'background-color'		=> '#09f',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '50%',
				'background-position-y'	=> '50%',
			],
			'url("/image/foo.jpg") no-repeat #09f center center' => [
				'background-color'		=> '#09f',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '50%',
				'background-position-y'	=> '50%',
			],
			'url("/image/foo.jpg") no-repeat #09f bottom center' => [
				'background-color'		=> '#09f',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '50%',
				'background-position-y'	=> '100%',
			],
			'url("/image/foo.jpg") repeat top center' => [
				'background-color'		=> 'transparent',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'repeat',
				'background-position-x'	=> '50%',
				'background-position-y'	=> '0%',
			],
			'url("/image/foo.jpg") no-repeat #09f bottom' => [
				'background-color'		=> '#09f',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '50%',
				'background-position-y'	=> '100%',
			],
			'url("/image/foo.jpg") no-repeat #09f 50%' => [
				'background-color'		=> '#09f',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '50%',
				'background-position-y'	=> '50%',
			],
			'#09f' => [
				'background-color'		=> '#09f',
				'background-image'		=> false,
				'background-repeat'		=> false,
				'background-position-x'	=> false,
				'background-position-y'	=> false,
			],
			'rgb(255,0,0)' => [
				'background-color'		=> '#FF0000',
				'background-image'		=> false,
				'background-repeat'		=> false,
				'background-position-x'	=> false,
				'background-position-y'	=> false,
			],
			'blue' => [
				'background-color'		=> '#0000FF',
				'background-image'		=> false,
				'background-repeat'		=> false,
				'background-position-x'	=> false,
				'background-position-y'	=> false,
			],
			'url("/image/foo.jpg") no-repeat rgb(0, 255, 0) 50%' => [
				'background-color'		=> '#00FF00',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '50%',
				'background-position-y'	=> '50%'
			],
			'url("/image/foo.jpg") no-repeat rgb(0, 255, 0) 50%' => [
				'background-color'		=> '#00FF00',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '50%',
				'background-position-y'	=> '50%',
			],
			'url("/image/foo.jpg") no-repeat red 50%' => [
				'background-color'		=> '#FF0000',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '50%',
				'background-position-y'	=> '50%',
			],
			'url("/image/foo.jpg") no-repeat red center 10px' => [
				'background-color'		=> '#FF0000',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '50%',
				'background-position-y'	=> '10px',
			],
			'url("/image/foo.jpg") no-repeat red 10px center' => [
				'background-color'		=> '#FF0000',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '10px',
				'background-position-y'	=> '50%',
			],
			'url("/image/foo.jpg") no-repeat red 10px 100%' => [
				'background-color'		=> '#FF0000',
				'background-image'		=> '/image/foo.jpg',
				'background-repeat'		=> 'no-repeat',
				'background-position-x'	=> '10px',
				'background-position-y'	=> '100%',
			],
		);

		foreach($backgrounds as $shorthand => $expected_result) {
			$style = new DImage\Style(['background' => $shorthand]);
			$actual_result = $style->settings;

			foreach($expected_result as $expected_key => $expected_value) {
				$actual_value = $actual_result[$expected_key];
				if ($expected_key === 'background-color' && preg_match('/^#/', $expected_value)) {
					$expected_value = DImage\Color::hexToRgb($expected_value);
					$actual_value = DImage\Color::hexToRgb($actual_value);
				}
				$this->assertEquals($expected_value, $actual_value, $shorthand);
			}
		}
	}

	public function testPaddingShorthand()
	{
		$style = new DImage\Style;
		unset($style->settings[DImage\Style::KEY_PADDING_TOP]);
		unset($style->settings[DImage\Style::KEY_PADDING_RIGHT]);
		unset($style->settings[DImage\Style::KEY_PADDING_BOTTOM]);
		unset($style->settings[DImage\Style::KEY_PADDING_LEFT]);

		$style = new DImage\Style([DImage\Style::KEY_PADDING => '1']);
		$this->assertEquals(1, (int) $style->settings[DImage\Style::KEY_PADDING_TOP]);
		$this->assertEquals(1, (int) $style->settings[DImage\Style::KEY_PADDING_RIGHT]);
		$this->assertEquals(1, (int) $style->settings[DImage\Style::KEY_PADDING_BOTTOM]);
		$this->assertEquals(1, (int) $style->settings[DImage\Style::KEY_PADDING_LEFT]);

		$style = new DImage\Style([DImage\Style::KEY_PADDING => '2 3']);
		$this->assertEquals(2, (int) $style->settings[DImage\Style::KEY_PADDING_TOP]);
		$this->assertEquals(3, (int) $style->settings[DImage\Style::KEY_PADDING_RIGHT]);
		$this->assertEquals(2, (int) $style->settings[DImage\Style::KEY_PADDING_BOTTOM]);
		$this->assertEquals(3, (int) $style->settings[DImage\Style::KEY_PADDING_LEFT]);

		$style = new DImage\Style([DImage\Style::KEY_PADDING => '4 5 6']);
		$this->assertEquals(4, (int) $style->settings[DImage\Style::KEY_PADDING_TOP]);
		$this->assertEquals(5, (int) $style->settings[DImage\Style::KEY_PADDING_RIGHT]);
		$this->assertEquals(6, (int) $style->settings[DImage\Style::KEY_PADDING_BOTTOM]);
		$this->assertEquals(5, (int) $style->settings[DImage\Style::KEY_PADDING_LEFT]);

		$style = new DImage\Style([DImage\Style::KEY_PADDING => '7 8 9 10']);
		$this->assertEquals(7, (int) $style->settings[DImage\Style::KEY_PADDING_TOP]);
		$this->assertEquals(8, (int) $style->settings[DImage\Style::KEY_PADDING_RIGHT]);
		$this->assertEquals(9, (int) $style->settings[DImage\Style::KEY_PADDING_BOTTOM]);
		$this->assertEquals(10, (int) $style->settings[DImage\Style::KEY_PADDING_LEFT]);
	}

	public function testFillFonts()
	{
		// Custom font
		$style = new DImage\Style([
			'font-family' => 'FooFont',
			'font-path' => '/foo.otf',
		]);
		$this->assertEquals('/foo.otf', $style->fontPath('FooFont'));

		// Default font
		$style = new DImage\Style([]);
		$this->assertTrue(boolval($style->fontPath('ChunkFive')));
	}
}
