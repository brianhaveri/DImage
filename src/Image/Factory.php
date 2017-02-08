<?php

namespace DImage\Image;

class Factory
{
	/**
	 * Get an instance of DImage\Image
	 * @param string $text
	 * @param array $style
	 * @return object Instance of DImage\Image
	 */
	public static function create($text, $style = [])
	{
		return new \DImage\Image($text, $style);
	}
}
