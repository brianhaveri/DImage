<?php

namespace DImage;

class Color
{
	/**
	 * Convert RGB values to hex
	 * @param integer $r Red (0 to 255)
	 * @param integer $g Green (0 to 255)
	 * @param integer $b Blue (0 to 255)
	 * @return string Hex
	 */
	public static function rgbToHex($r, $g, $b)
	{
		return sprintf('%02X%02X%02X', $r, $g, $b);
	}

	/**
	 * Convert hex to RGB values
	 * @param string $hex Hex (3 or 6 chars, with or without leading #)
	 * @return array RGB values with 'r', 'g', and 'b' keys
	 */
	public static function hexToRgb($hex)
	{
		$hex = preg_replace('/[^a-zA-Z0-9]/', '', $hex);
		if(strlen($hex) === 3) {
			$hex = preg_replace('/([a-f0-9]{1})/i', "$1$1", $hex);
		}
		if (!strlen($hex)) {
			return ['r'=>null, 'g'=>null, 'b'=>null];
		}

		list($r, $g, $b) = array_map('hexdec', str_split($hex, 2));
		return ['r'=>$r, 'g'=>$g, 'b'=>$b];
	}


	/**
	 * Convert color name to hex
	 * @param string $color Color
	 * @return string
	 */
	public static function colorToHex($color)
	{
		$color = strtolower($color);
		static $colors = [
			'maroon'	=> '800000',
			'red'		=> 'FF0000',
			'orange'	=> 'FFA500',
			'yellow'	=> 'FFFF00',
			'olive'		=> '808000',
			'purple'	=> '800080',
			'fuchsia'	=> 'FF00FF',
			'white'		=> 'FFFFFF',
			'lime'		=> '00FF00',
			'green'		=> '008000',
			'navy'		=> '000080',
			'blue'		=> '0000FF',
			'aqua'		=> '00FFFF',
			'teal'		=> '008080',
			'black'		=> '000000',
			'silver'	=> 'C0C0C0',
			'gray'		=> '808080',
		];
		if(array_key_exists($color, $colors)) {
			return $colors[$color];
		}

		$color_regex = '/rgb\((.*?)\)/';
		preg_match($color_regex, $color, $matches);
		if(is_array($matches) && count($matches) > 0) {
			$rgb = explode(',', $matches[1]);
			if(is_array($rgb) && count($rgb) === 3) {
				return self::rgbToHex($rgb[0], $rgb[1], $rgb[2]);
			}
		}

		return $color;
	}

	/**
	 * Get a random hex
	 * @return string
	 */
	public static function randomHex()
	{
		return substr(md5(mt_rand()), 0, 6);
	}
}
