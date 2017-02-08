<?php

class ImageTest extends PHPUnit_Framework_TestCase
{
	public function testGenerate()
	{
		$style = new DImage\Style([]);
		$image = new DImage\Image(__FUNCTION__, $style);
		$this->assertTrue($style instanceof DImage\Style);
		$this->assertTrue($image instanceof DImage\Image);
		$generate_result = $image->generate();
		$this->assertTrue($generate_result instanceof DImage\Image);
	}

	public function testSaveJpg()
	{
		$file_path = __DIR__.'/cache/'.__FUNCTION__.'.jpg';
		if (file_exists($file_path)) {
			unlink($file_path);
			$this->assertFalse(file_exists($file_path));
		}

		$style = new DImage\Style(['type'=>'jpg']);
		$image = new DImage\Image(__FUNCTION__, $style);
		$image->generate();
		$save_result = $image->save($file_path);
		$this->assertTrue($save_result);
		$this->assertTrue(file_exists($file_path));
		$this->assertTrue(filesize($file_path) > 0);
	}

	public function testSaveJpeg()
	{
		$file_path = __DIR__.'/cache/'.__FUNCTION__.'.jpeg';
		if (file_exists($file_path)) {
			unlink($file_path);
			$this->assertFalse(file_exists($file_path));
		}

		$style = new DImage\Style(['type'=>'jpeg']);
		$image = new DImage\Image(__FUNCTION__, $style);
		$image->generate();
		$save_result = $image->save($file_path);
		$this->assertTrue($save_result);
		$this->assertTrue(file_exists($file_path));
		$this->assertTrue(filesize($file_path) > 0);
	}

	public function testSaveGif()
	{
		$file_path = __DIR__.'/cache/'.__FUNCTION__.'.gif';
		if (file_exists($file_path)) {
			unlink($file_path);
			$this->assertFalse(file_exists($file_path));
		}

		$style = new DImage\Style(['type'=>'gif']);
		$image = new DImage\Image(__FUNCTION__, $style);
		$image->generate();
		$save_result = $image->save($file_path);
		$this->assertTrue($save_result);
		$this->assertTrue(file_exists($file_path));
		$this->assertTrue(filesize($file_path) > 0);
	}

	public function testSavePng()
	{
		$file_path = __DIR__.'/cache/'.__FUNCTION__.'.png';
		if (file_exists($file_path)) {
			unlink($file_path);
			$this->assertFalse(file_exists($file_path));
		}

		$style = new DImage\Style(['type'=>'png']);
		$image = new DImage\Image(__FUNCTION__, $style);
		$image->generate();
		$save_result = $image->save($file_path);
		$this->assertTrue($save_result);
		$this->assertTrue(file_exists($file_path));
		$this->assertTrue(filesize($file_path) > 0);
	}

	public function testCurry()
	{
		$file_path = __DIR__.'/cache/'.__FUNCTION__.'.png';
		if (file_exists($file_path)) {
			unlink($file_path);
			$this->assertFalse(file_exists($file_path));
		}

		$result = (new DImage\Image)
			->setText(__FUNCTION__)
			->setStyle(new DImage\Style([]))
			->generate()
			->save($file_path);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($file_path));
		$this->assertTrue(filesize($file_path) > 0);
	}

	public function testOutputBackgroundColor()
	{
		$colors = [
			'#00f' => [0, 0, 255],
			'#f00' => [255, 0, 0],
			'#0f0' => [0, 255, 0],
		];
		foreach ($colors as $hex => $rgb) {
			// File. PNG is easier to match colors because purity.
			$file_path = __DIR__.'/cache/'.__FUNCTION__.'-'.$hex.'.png';
			if (file_exists($file_path)) {
				unlink($file_path);
				$this->assertFalse(file_exists($file_path));
			}

			// Image
			$result = (new DImage\Image)
				->setText(__FUNCTION__)
				->setStyle(new DImage\Style([
					'background-color' => $hex,
				]))
				->generate()
				->save($file_path);

			// Output
			$image_resource = imagecreatefrompng($file_path);
			$background_rgb = array_values(imagecolorsforindex($image_resource, imagecolorat($image_resource, 0, 0)));
			$this->assertEquals($rgb[0], $background_rgb[0]);
			$this->assertEquals($rgb[1], $background_rgb[1]);
			$this->assertEquals($rgb[2], $background_rgb[2]);
		}
	}

	public function testOutputTextColor()
	{
		$colors = [
			'#00f' => [0, 0, 255],
			'#f00' => [255, 0, 0],
			'#0f0' => [0, 255, 0],
		];
		foreach ($colors as $hex => $rgb) {
			// File. PNG is easier to match colors because purity.
			$file_path = __DIR__.'/cache/'.__FUNCTION__.'-'.$hex.'.png';
			if (file_exists($file_path)) {
				unlink($file_path);
				$this->assertFalse(file_exists($file_path));
			}

			// Image
			$result = (new DImage\Image)
				->setText(__FUNCTION__)
				->setStyle(new DImage\Style([
					'background-color' => 'white',
					'color' => $hex,
					'height' => 300,
					'vertical-align' => 'middle',
				]))
				->generate()
				->save($file_path);

			// Output
			$image_resource = imagecreatefrompng($file_path);
			$text_rgb = array_values(imagecolorsforindex($image_resource, imagecolorat($image_resource, 20, 150)));
			$this->assertEquals($rgb[0], $text_rgb[0]);
			$this->assertEquals($rgb[1], $text_rgb[1]);
			$this->assertEquals($rgb[2], $text_rgb[2]);
		}
	}
}
