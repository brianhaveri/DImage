<?php

namespace DImage;

class Config
{
    // Default styles
    const DEFAULT_COLOR = '000';
    const DEFAULT_FONT_FAMILY = 'ChunkFive';
    const DEFAULT_FONT_SIZE = 20;
    const DEFAULT_FONT_PATH = null;
    const DEFAULT_MAX_WIDTH = 900;
    const DEFAULT_LINE_HEIGHT = 1;        			// Set accordingly based on font-family
    const DEFAULT_PADDING_TOP = 10;
    const DEFAULT_PADDING_RIGHT = 10;
    const DEFAULT_PADDING_BOTTOM = 10;
    const DEFAULT_PADDING_LEFT = 0;
    const DEFAULT_TEXT_ALIGN = 'left';    			// left, center, right
    const DEFAULT_VERTICAL_ALIGN = 'top';    		// top, middle, bottom
    const DEFAULT_BACKGROUND = FALSE;    			// CSS string or FALSE
    const DEFAULT_BACKGROUND_OPACITY = 100;        	// 0 to 100. Only applies to png images.
    const DEFAULT_BACKGROUND_COLOR = FALSE;    		// Hex or FALSE
    const DEFAULT_BACKGROUND_IMAGE = FALSE;    		// URL or FALSE
    const DEFAULT_BACKGROUND_REPEAT = FALSE;    	// CSS string or FALSE
    const DEFAULT_BACKGROUND_POSITION = FALSE;    	// CSS string or FALSE
    const DEFAULT_BACKGROUND_POSITION_X = FALSE;    // CSS string or FALSE
    const DEFAULT_BACKGROUND_POSITION_Y = FALSE;    // CSS string or FALSE
    const DEFAULT_HEIGHT = FALSE;    				// Integer or FALSE
    const DEFAULT_TEXT_TRANSFORM = FALSE;    		// CSS string or FALSE
    const DEFAULT_WIDTH = FALSE;    				// Integer or FALSE
    const DEFAULT_LETTER_SPACING = 0;        		// Double (works like em, not px)
    const DEFAULT_TYPE = 'png';    					// png, jpg, or gif
    const DEFAULT_EXTENSION = '.png';    			// Should correspond to DIMAGE_DEFAULT_TYPE
    const DEFAULT_QUALITY = 100;					// 0 to 100. Only applies to jpg and png images.

    public $settings = [];

    /**
     * Create the instance
     * @param array $settings
     */
    public function __construct($settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * Get an individual setting
     * @param string $key
     * @return mixed
     */
    public function getSetting($key)
    {
        return (array_key_exists($key, $this->settings))
            ? $this->settings[$key]
            : null;
    }
}