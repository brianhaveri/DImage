<?php

namespace DImage;

class Style
{
	const KEY_COLOR = 'color';
    const KEY_FONT_FAMILY = 'font-family';
    const KEY_FONT_SIZE = 'font-size';
    const KEY_FONT_PATH = 'font-path';
    const KEY_MAX_WIDTH = 'max-width';
    const KEY_LINE_HEIGHT = 'line-height';
    const KEY_PADDING = 'padding';
    const KEY_PADDING_TOP = 'padding-top';
    const KEY_PADDING_RIGHT = 'padding-right';
    const KEY_PADDING_BOTTOM = 'padding-bottom';
    const KEY_PADDING_LEFT = 'padding-left';
    const KEY_WIDTH = 'width';
    const KEY_TEXT = 'text';
    const KEY_BACKGROUND = 'background';
    const KEY_BACKGROUND_OPACITY = 'background-opacity';
    const KEY_BACKGROUND_COLOR = 'background-color';
    const KEY_BACKGROUND_IMAGE = 'background-image';
    const KEY_BACKGROUND_REPEAT = 'background-repeat';
    const KEY_BACKGROUND_POSITION = 'background-position';
    const KEY_BACKGROUND_POSITION_X = 'background-position-x';
    const KEY_BACKGROUND_POSITION_Y = 'background-position-y';
    const KEY_HEIGHT = 'height';
    const KEY_VERTICAL_ALIGN = 'vertical-align';
    const KEY_TEXT_ALIGN = 'text-align';
    const KEY_TEXT_TRANSFORM = 'text-transform';
    const KEY_SPRITES = 'sprites';
    const KEY_SPRITE = 'sprite';
    const KEY_LETTER_SPACING = 'letter-spacing';
    const KEY_TYPE = 'type';
    const KEY_EXTENSION = 'extension';
    const KEY_QUALITY = 'quality';

    public $parent_style = null;
    public $settings = [];
    public $fonts = [];
    private $_shorthand_vals = [];

    /**
     * Create an instance
     * @param array $settings
     */
    public function __construct($settings = [])
    {
    	$this->settings = $settings;
        $this->fillStyle();
    }

	/**
	 * Convert position name to percent
	 * @param string $position
	 * @return string Percent
	 */
	public static function positionToPercent($position)
	{
		$positions = [
			'left'	=> '0%',
			'right'	=> '100%',
			'top'	=> '0%',
			'bottom'=> '100%',
			'center'=> '50%',
		];
		return array_key_exists($position, $positions)
			? $positions[$position]
			: $position;
	}

	/**
     * Add any missing but required style key/values
     * @return array Style
     */
    public function fillStyle()
    {
        // Assign file type
        $this->fillStyleType();

        // Assign file extension
        $this->fillStyleExtension();

        // Assign image quality
        $this->fillStyleQuality();

        // Handle background. This allows for CSS shorthand.
        $this->fillStyleBackground();

        // Handle padding. This allows applying self::KEY_PADDING to all sides (or just some)
        $this->fillStylePadding();

        // Width should override max-width, just like CSS
        if (array_key_exists(self::KEY_WIDTH, $this->settings) && $this->settings[self::KEY_WIDTH]) {
            $this->settings[self::KEY_MAX_WIDTH] = $this->settings[self::KEY_WIDTH];
        }

        // Inherit styles from parent. Used for sprite generation.
        $parent_style_settings = ($this->parent_style) ? $this->parent_style->settings : [];
        if (is_array($this->parent_style) || count($this->parent_style) > 0) {
            foreach ($parent_style_settings as $k => $v) {
                if (!array_key_exists($k, $this->settings)) {
                    $this->settings[$k] = $v;
                }
            }
        }

        // Fall back on defaults
        $default_settings = [
        	self::KEY_TYPE					=> Config::DEFAULT_TYPE,
            self::KEY_COLOR                 => Config::DEFAULT_COLOR,
            self::KEY_FONT_FAMILY           => Config::DEFAULT_FONT_FAMILY,
            self::KEY_FONT_SIZE             => Config::DEFAULT_FONT_SIZE,
            self::KEY_FONT_PATH             => Config::DEFAULT_FONT_PATH,
            self::KEY_MAX_WIDTH             => Config::DEFAULT_MAX_WIDTH,
            self::KEY_LINE_HEIGHT           => Config::DEFAULT_LINE_HEIGHT,
            self::KEY_PADDING_TOP           => Config::DEFAULT_PADDING_TOP,
            self::KEY_PADDING_RIGHT         => Config::DEFAULT_PADDING_RIGHT,
            self::KEY_PADDING_BOTTOM        => Config::DEFAULT_PADDING_BOTTOM,
            self::KEY_PADDING_LEFT          => Config::DEFAULT_PADDING_LEFT,
            self::KEY_BACKGROUND_OPACITY    => Config::DEFAULT_BACKGROUND_OPACITY,
            self::KEY_BACKGROUND_COLOR      => Config::DEFAULT_BACKGROUND_COLOR,
            self::KEY_BACKGROUND_IMAGE      => Config::DEFAULT_BACKGROUND_IMAGE,
            self::KEY_BACKGROUND_REPEAT     => Config::DEFAULT_BACKGROUND_REPEAT,
            self::KEY_BACKGROUND_POSITION   => Config::DEFAULT_BACKGROUND_POSITION,
            self::KEY_BACKGROUND_POSITION_X => Config::DEFAULT_BACKGROUND_POSITION_X,
            self::KEY_BACKGROUND_POSITION_Y => Config::DEFAULT_BACKGROUND_POSITION_Y,
            self::KEY_HEIGHT                => Config::DEFAULT_HEIGHT,
            self::KEY_WIDTH                 => Config::DEFAULT_WIDTH,
            self::KEY_VERTICAL_ALIGN        => Config::DEFAULT_VERTICAL_ALIGN,
            self::KEY_TEXT_TRANSFORM        => Config::DEFAULT_TEXT_TRANSFORM,
            self::KEY_TEXT_ALIGN            => Config::DEFAULT_TEXT_ALIGN,
            self::KEY_LETTER_SPACING        => Config::DEFAULT_LETTER_SPACING,
        ];
        foreach ($default_settings as $k => $v) {
            if (array_key_exists($k, $this->settings)) {
                continue;
            }
            $this->settings[$k] = $v;
        }

        // Handle font family and paths
        $this->fillStyleFonts();

        // Convert color strings to hex if necessary
        $this->settings[self::KEY_COLOR] = Color::colorToHex($this->settings[self::KEY_COLOR]);
        $this->settings[self::KEY_BACKGROUND_COLOR] = Color::colorToHex($this->settings[self::KEY_BACKGROUND_COLOR]);

        return $this;
    }

    /**
     * Assign file type
     * @return object Style
     */
    public function fillStyleType()
    {
        $parent_style_settings = ($this->parent_style) ? $this->parent_style->settings : [];
        if (!array_key_exists(self::KEY_TYPE, $this->settings)) {
            $this->settings[self::KEY_TYPE] = (is_array($this->parent_style) && array_key_exists(self::KEY_TYPE, $parent_style_settings))
                ? $parent_style_settings[self::KEY_TYPE]
                : Config::DEFAULT_TYPE;
        }
        return $this;
    }

    /**
     * Assign file extension
     * @return object Style
     */
    public function fillStyleExtension()
    {
        $parent_style_settings = ($this->parent_style) ? $this->parent_style->settings : [];
        if (array_key_exists(self::KEY_EXTENSION, $parent_style_settings)) {
            return $this;
        }

        if (is_array($this->parent_style) && array_key_exists(self::KEY_EXTENSION, $parent_style_settings)) {
            $this->settings[self::KEY_EXTENSION] = $parent_style_settings[self::KEY_EXTENSION];
            return $this;
        }

        if (array_key_exists(self::KEY_TYPE, $this->settings)) {
            switch ($this->settings[self::KEY_TYPE]) {
                case 'png':
                    $this->settings[self::KEY_EXTENSION] = '.png';
                    break;
                case 'jpeg':
                    $this->settings[self::KEY_EXTENSION] = '.jpg';
                    break;
                case 'jpg':
                    $this->settings[self::KEY_EXTENSION] = '.jpg';
                    break;
                case 'gif':
                    $this->settings[self::KEY_EXTENSION] = '.gif';
                    break;
            }
        }
        if (!array_key_exists(self::KEY_TYPE, $this->settings)) {
            $this->settings[self::KEY_EXTENSION] = (defined(Config::DEFAULT_EXTENSION))
                ? Config::DEFAULT_EXTENSION
                : '.'.Config::DEFAULT_TYPE;
        }
        return $this;
    }

    /**
     * Assign image quality
     * @return object Style
     */
    public function fillStyleQuality()
    {
        $parent_style_settings = ($this->parent_style) ? $this->parent_style->settings : [];
        if (!array_key_exists(self::KEY_QUALITY, $this->settings)) {
            if (is_array($this->parent_style) && array_key_exists(self::KEY_QUALITY, $parent_style_settings)) {
                $this->settings[self::KEY_QUALITY] = $parent_style_settings[self::KEY_QUALITY];
                return $this->settings;
            }
            $this->settings[self::KEY_QUALITY] = Config::DEFAULT_QUALITY;
        }
        switch($this->settings[self::KEY_TYPE]) {
            case 'png' :
                $this->settings[self::KEY_QUALITY] = 9 - round(9 * ($this->settings[self::KEY_QUALITY] / 100));
                break;
        }
        return $this;
    }

    /**
     * Assign background. Allow for normal CSS assignments including shorthand.
     * @return array
     */
    public function fillStyleBackground()
    {
        $parent_style_settings = ($this->parent_style) ? $this->parent_style->settings : [];

        $background = array_key_exists(self::KEY_BACKGROUND, $this->settings)
            ? $this->settings[self::KEY_BACKGROUND]
            : '';

        // background-image
        $has_background_image = false;
        if (!array_key_exists(self::KEY_BACKGROUND_IMAGE, $this->settings) && preg_match('/url\((.*?)\)/', $background, $image_matches)) {
            $this->settings[self::KEY_BACKGROUND_IMAGE] = preg_replace('/^[\'"]{1,}(.*)[\'"]{1,}$/', "$1", $image_matches[1]);
            $background = str_replace($image_matches, '', $background);
            $has_background_image = true;
        } elseif (array_key_exists(self::KEY_BACKGROUND_IMAGE, $this->settings)) {
            preg_match('/url\((.*?)\)/', $this->settings[self::KEY_BACKGROUND_IMAGE], $image_matches);
            if (is_array($image_matches) && count($image_matches) > 0) {
                $this->settings[self::KEY_BACKGROUND_IMAGE] = preg_replace(
                    '/^[\'"]{1,}(.*)[\'"]{1,}$/',
                    "$1",
                    $image_matches[1]
                );
                $has_background_image = true;
            }
        }
        if (
            !$has_background_image
            && is_array($this->parent_style)
            && array_key_exists(self::KEY_BACKGROUND_IMAGE, $parent_style_settings)
        ) {
            $has_background_image = true;
        }

        // background-color if set as rgb(r,g,b)
        $color_regex = '/rgb\((.*?)\)/';
        $background_color = (array_key_exists(self::KEY_BACKGROUND_COLOR, $this->settings) && $this->settings[self::KEY_BACKGROUND_COLOR])
            ? $this->settings[self::KEY_BACKGROUND_COLOR]
            : $background;
        preg_match($color_regex, $background_color, $rgb_matches);
        if (is_array($rgb_matches) && count($rgb_matches) > 0) {
            $rgb = explode(',', $rgb_matches[1]);
            if (is_array($rgb) && count($rgb) === 3) {
                $this->settings[self::KEY_BACKGROUND_COLOR] = Color::rgbToHex($rgb[0], $rgb[1], $rgb[2]);
            }
            $background = str_replace($rgb_matches[0], '', $background);
        }

        // Make sure you do this after background-color by rgb(r,g,b)
        // Otherwise the explode won't work
        $background = preg_replace('/[\s]{2,}/', ' ', $background);
        $this->_shorthand_vals = explode(' ', $background);
        if (!is_array($this->_shorthand_vals) || count($this->_shorthand_vals) === 0) {
            return $this;
        }

        // background-repeat and background-position are only set if background-image present
        if ($has_background_image) {
            $this->fillStyleBackgroundImage();
        }

        // If anything, only background-color and blanks remain in vals array. Remove blanks.
        if (
            !array_key_exists(self::KEY_BACKGROUND_COLOR, $this->settings)
            || !$this->settings[self::KEY_BACKGROUND_COLOR]
        ) {
            $this->_shorthand_vals = array_unique($this->_shorthand_vals);
            sort($this->_shorthand_vals);
            array_walk($this->_shorthand_vals, 'trim');
            $this->_shorthand_vals = array_filter($this->_shorthand_vals, 'strlen');
            $this->_shorthand_vals = array_values($this->_shorthand_vals);
            if (count($this->_shorthand_vals) > 0) {
                $this->settings[self::KEY_BACKGROUND_COLOR] = $this->_shorthand_vals[0];
                $this->settings[self::KEY_BACKGROUND_COLOR] = Color::colorToHex($this->settings[self::KEY_BACKGROUND_COLOR]);
            } elseif (array_key_exists(self::KEY_BACKGROUND_COLOR, $parent_style_settings)) {
                $this->settings[self::KEY_BACKGROUND_COLOR] = $parent_style_settings[self::KEY_BACKGROUND_COLOR];
            } elseif (is_array($this->parent_style) && array_key_exists(self::KEY_BACKGROUND_COLOR, $parent_style_settings)) {
                $this->settings[self::KEY_BACKGROUND_COLOR] = $parent_style_settings[self::KEY_BACKGROUND_COLOR];
            } else {
                $this->settings[self::KEY_BACKGROUND_COLOR] = 'transparent';
            }
        }

        if (is_array($this->parent_style)) {
            $background_attribs = array(
                self::KEY_BACKGROUND_OPACITY,
                self::KEY_BACKGROUND_IMAGE,
                self::KEY_BACKGROUND_REPEAT,
                self::KEY_BACKGROUND_POSITION,
                self::KEY_BACKGROUND_POSITION_X,
                self::KEY_BACKGROUND_POSITION_Y
            );
            foreach ($background_attribs as $attrib_key) {
                if (
                    (!array_key_exists($attrib_key, $this->settings) || $this->settings[$attrib_key] === false)
                    && array_key_exists($attrib_key, $parent_style_settings)
                ) {
                    $this->settings[$attrib_key] = $parent_style_settings[$attrib_key];
                }
            }
        }
        return $this->settings;
    }

    /**
     * Assign background image
     * @param array $this->_shorthand_vals Values from CSS shorthand for 'background' attribute
     * @return object Instance of Style
     */
    public function fillStyleBackgroundImage()
    {
        $parent_style_settings = ($this->parent_style) ? $this->parent_style->settings : [];

        // Only set to false if vals are empty (not set by this style or parent style)
        $image_attribs = [
            self::KEY_BACKGROUND_REPEAT,
            self::KEY_BACKGROUND_POSITION_Y,
            self::KEY_BACKGROUND_POSITION_X,
        ];
        foreach ($image_attribs as $image_attrib) {
            if (!array_key_exists($image_attrib, $this->settings)) {
                $this->settings[$image_attrib] = false;
            }
        }

        // background-repeat
        $pattern = '(repeat-x|repeat-y|no-repeat|repeat)';
        $repeats = preg_grep($pattern, $this->_shorthand_vals);
        if ($repeats) {
            $repeats = array_values($repeats);
            $this->settings[self::KEY_BACKGROUND_REPEAT] = $repeats[0];
            $this->_shorthand_vals = preg_replace($pattern, '', $this->_shorthand_vals);
        }
        if ($this->settings[self::KEY_BACKGROUND_IMAGE] && !$this->settings[self::KEY_BACKGROUND_REPEAT]) {
            $this->settings[self::KEY_BACKGROUND_REPEAT] = (is_array($this->parent_style) && array_key_exists(self::KEY_BACKGROUND_REPEAT, $parent_style_settings))
                ? $parent_style_settings[self::KEY_BACKGROUND_REPEAT]
                : 'repeat';
        }

        // background-position
        if (array_key_exists(self::KEY_BACKGROUND_POSITION, $this->settings)) {
            $positions = explode(' ', $this->settings[self::KEY_BACKGROUND_POSITION]);
            if (count($positions) === 2) {
                if (in_array($positions[0], array('left', 'right'))) {
                    $this->settings[self::KEY_BACKGROUND_POSITION_X] = Style::positionToPercent($positions[0]);
                    $this->settings[self::KEY_BACKGROUND_POSITION_Y] = Style::positionToPercent($positions[1]);
                }
                else {
                    $this->settings[self::KEY_BACKGROUND_POSITION_X] = Style::positionToPercent($positions[1]);
                    $this->settings[self::KEY_BACKGROUND_POSITION_Y] = Style::positionToPercent($positions[0]);
                }
            }
            elseif (count($positions) === 1) {
                $this->settings[self::KEY_BACKGROUND_POSITION_X] = Style::positionToPercent($positions[0]);
                $this->settings[self::KEY_BACKGROUND_POSITION_Y] = Style::positionToPercent($positions[0]);
            }
        }

        // background-position x
        $pattern = '/(left|right)/';
        $positions_x = preg_grep($pattern, $this->_shorthand_vals);
        if ($positions_x) {
            $positions_x = array_values($positions_x);
            $this->settings[self::KEY_BACKGROUND_POSITION_X] = Style::positionToPercent($positions_x[0]);
            $this->_shorthand_vals = preg_replace($pattern, '', $this->_shorthand_vals);
        }

        // background-position y
        $pattern = '/(top|bottom)/';
        $positions_y = preg_grep($pattern, $this->_shorthand_vals);
        if ($positions_y) {
            $positions_y = array_values($positions_y);
            $this->settings[self::KEY_BACKGROUND_POSITION_Y] = Style::positionToPercent($positions_y[0]);
            $this->_shorthand_vals = preg_replace($pattern, '', $this->_shorthand_vals);
        }

        if (
            $this->settings[self::KEY_BACKGROUND_IMAGE]
            && (!$this->settings[self::KEY_BACKGROUND_POSITION_X] || !$this->settings[self::KEY_BACKGROUND_POSITION_Y])
        ) {
            $pattern_xy = '/(^[-\d]{1,}(?:\s|%|px){0,}|center)/';
            $positions = preg_grep($pattern_xy, $this->_shorthand_vals);
            if (is_array($positions) && count($positions) > 0) {
                $positions = array_values($positions);
                if (count($positions) === 1) {
                    $this->settings[self::KEY_BACKGROUND_POSITION_X] = ($this->settings[self::KEY_BACKGROUND_POSITION_X] !== false)
                        ? $this->settings[self::KEY_BACKGROUND_POSITION_X]
                        : Style::positionToPercent($positions[0]);
                    $this->settings[self::KEY_BACKGROUND_POSITION_Y] = ($this->settings[self::KEY_BACKGROUND_POSITION_Y] !== false)
                        ? $this->settings[self::KEY_BACKGROUND_POSITION_Y]
                        : Style::positionToPercent($positions[0]);
                }
                else {
                    $this->settings[self::KEY_BACKGROUND_POSITION_X] = Style::positionToPercent($positions[0]);
                    $this->settings[self::KEY_BACKGROUND_POSITION_Y] = Style::positionToPercent($positions[1]);
                }
                $this->_shorthand_vals = preg_replace($pattern_xy, '', $this->_shorthand_vals);
            }

            // No background-position defined, default to parent style or 0, 0
            if (!$this->settings[self::KEY_BACKGROUND_POSITION_X]) {
                $this->settings[self::KEY_BACKGROUND_POSITION_X] = (is_array($this->parent_style) && array_key_exists(self::KEY_BACKGROUND_POSITION_X, $parent_style_settings))
                    ? $parent_style_settings[self::KEY_BACKGROUND_POSITION_X]
                    : 0;
            }
            if (!$this->settings[self::KEY_BACKGROUND_POSITION_Y]) {
                $this->settings[self::KEY_BACKGROUND_POSITION_Y] = (is_array($this->parent_style) && array_key_exists(self::KEY_BACKGROUND_POSITION_Y, $parent_style_settings))
                    ? $parent_style_settings[self::KEY_BACKGROUND_POSITION_Y]
                    : 0;
            }
        }

        if (
            $this->settings[self::KEY_BACKGROUND_IMAGE]
            && !$this->settings[self::KEY_BACKGROUND_POSITION_X]
            && $this->settings[self::KEY_BACKGROUND_POSITION_Y]
        ) {
            $this->settings[self::KEY_BACKGROUND_POSITION_X] = '50%';
        } elseif (
            $this->settings[self::KEY_BACKGROUND_IMAGE]
            && $this->settings[self::KEY_BACKGROUND_POSITION_Y]
            && !$this->settings[self::KEY_BACKGROUND_POSITION_Y]
        ) {
            $this->settings[self::KEY_BACKGROUND_POSITION_Y] = '50%';
        }

        $this->settings[self::KEY_BACKGROUND_POSITION_X] = (array_key_exists(self::KEY_BACKGROUND_POSITION_X, $this->settings) && $this->settings[self::KEY_BACKGROUND_POSITION_X] !== false)
            ? Style::positionToPercent($this->settings[self::KEY_BACKGROUND_POSITION_X])
            : false;
        $this->settings[self::KEY_BACKGROUND_POSITION_Y] = (array_key_exists(self::KEY_BACKGROUND_POSITION_Y, $this->settings) && $this->settings[self::KEY_BACKGROUND_POSITION_Y] !== false)
            ? Style::positionToPercent($this->settings[self::KEY_BACKGROUND_POSITION_Y])
            : false;

        return $this;
    }

    /**
     * Assign paddings. Allow for normal CSS assignments including shorthand.
     * This only assigns padding if self::KEY_PADDING is populated or a specific padding assigned.
     * fillStyle() does any remaining padding assignment by inheritance and defaults.
     * @return object Style
     */
    public function fillStylePadding()
    {
        $parent_style_settings = ($this->parent_style) ? $this->parent_style->settings : [];

        if (!array_key_exists(self::KEY_PADDING, $this->settings)) {
            return $this->settings;
        }

        $this->settings = $this->settings;
        $padding = preg_replace('/[\s]{1,}/', ' ', trim($this->settings[self::KEY_PADDING]));
        $padding = preg_replace('/[^\d\s]/', '', $padding);
        $padding_segments = explode(' ', $padding);
        switch(count($padding_segments)) {
            case 1:
                $new_settings[self::KEY_PADDING_TOP]      = $padding_segments[0];
                $new_settings[self::KEY_PADDING_RIGHT]    = $padding_segments[0];
                $new_settings[self::KEY_PADDING_BOTTOM]   = $padding_segments[0];
                $new_settings[self::KEY_PADDING_LEFT]     = $padding_segments[0];
                break;
            case 2:
                $new_settings[self::KEY_PADDING_TOP]      = $padding_segments[0];
                $new_settings[self::KEY_PADDING_RIGHT]    = $padding_segments[1];
                $new_settings[self::KEY_PADDING_BOTTOM]   = $padding_segments[0];
                $new_settings[self::KEY_PADDING_LEFT]     = $padding_segments[1];
                break;
            case 3:
                $new_settings[self::KEY_PADDING_TOP]      = $padding_segments[0];
                $new_settings[self::KEY_PADDING_RIGHT]    = $padding_segments[1];
                $new_settings[self::KEY_PADDING_BOTTOM]   = $padding_segments[2];
                $new_settings[self::KEY_PADDING_LEFT]     = $padding_segments[1];
                break;
            case 4:
                $new_settings[self::KEY_PADDING_TOP]      = $padding_segments[0];
                $new_settings[self::KEY_PADDING_RIGHT]    = $padding_segments[1];
                $new_settings[self::KEY_PADDING_BOTTOM]   = $padding_segments[2];
                $new_settings[self::KEY_PADDING_LEFT]     = $padding_segments[3];
                break;
        }
        if (count($new_settings) > 0) {
            foreach ($new_settings as $padding_key => $padding_val) {
                if (!array_key_exists($padding_key, $this->settings)) {
                    $this->settings[$padding_key] = $padding_val;
                }
            }
        }
        return $this;
    }

    /**
     * Fill the style with fonts
     * @return object Instance of Style
     */
    public function fillStyleFonts()
    {
        if (!array_key_exists(self::KEY_FONT_FAMILY, $this->settings)) {
            return $this;
        }
        if (!array_key_exists(self::KEY_FONT_PATH, $this->settings)) {
            return $this;
        }

        if ($this->settings[self::KEY_FONT_FAMILY]) {
            // fonts in assets
            $font_path = ($this->settings[self::KEY_FONT_PATH])
                ? $this->settings[self::KEY_FONT_PATH]
                : realpath(sprintf('%s/../assets/fonts/%s.otf', __DIR__, $this->settings[self::KEY_FONT_FAMILY]));

            $this->addFont(
                $this->settings[self::KEY_FONT_FAMILY],
                $font_path
            );
        }

        return $this;
    }

    /**
     * Get the font path
     * @param string $font_family
     * @return string Path
     */
    public function fontPath($font_family)
    {
        return (array_key_exists($font_family, $this->fonts))
            ? $this->fonts[$font_family]
            : null;
    }

    /**
     * Add a font
     * @param string $font_family
     * @param string $font_file_path
     * @return object Instance of style
     */
    public function addFont($font_family, $font_file_path)
    {
        $this->fonts[$font_family] = $font_file_path;
    }

}
