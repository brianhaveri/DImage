<?php

namespace DImage;

class Factory
{
	/**
	 * Convenient way to create a DImage front to back
	 * @param string $text
	 * @param array $style_settings
	 * @param string $file_path
	 * @return object Instance of Image
	 */
	public static function create($text, $style_settings = [], $file_path = null)
	{
		$style = new Style($style_settings);
		$image = new Image($text, $style);
		$image->generate();
		if ($file_path) {
			$image->save($file_path);
		}
		return $image;
	}
}
