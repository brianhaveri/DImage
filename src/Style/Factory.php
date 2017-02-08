<?php

namespace DImage\Style;

class Factory
{
	/**
	 * Create multiple Styles from JSON
	 * @param string $json The actual JSON data, not file path
	 * @return array
	 */
	public static function createFromJson($json)
	{
		$settings = json_decode($json);
		return ($settings)
			? self::createMultiple($settings)
			: [];
	}

	/**
	 * Create multiple instances of Style
	 * @param array $nested_settings
	 * @return array
	 */
	public static function createMultiple($nested_settings)
	{
		if (!$nested_settings) {
			return [];
		}

		$out = [];
		foreach ($nested_settings as $settings) {
			$out[] = self::create($settings);
		}
		return $out;
	}

	/**
	 * Create an instance of Style
	 * @param array $settings
	 * @return object Instance of Style
	 */
	public static function create($settings)
	{
		return new \DImage\Style($settings);
	}
}