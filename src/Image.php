<?php

namespace DImage;

class Image
{
    public $text = '';
    public $style = null;
    public $image = null;

    /**
     * Create an image
     * @param string $text
     * @param object $style Instance of Style
     */
    public function __construct($text = '', $style = null)
    {
        $this->setText($text);

        $style = ($style) ? $style : Style\Factory::create([]);
        $this->setStyle($style);
    }

    /**
     * Save your image to file
     * @param string $file_path
     * @return bool
     */
    public function save($file_path)
    {
        $style_settings = $this->style->settings;

        switch ($style_settings[Style::KEY_TYPE]) {
            case 'png':
                $res = imagepng($this->image, $file_path, $style_settings[Style::KEY_QUALITY]);
                break;
            case 'jpeg':
            case 'jpg':
                $res = imagejpeg($this->image, $file_path, $style_settings[Style::KEY_QUALITY]);
                break;
            case 'gif':
                $res = imagegif($this->image, $file_path);
                break;
            default:
                $res = false;
        }
        return $res;
    }

    /**
     * Generate the image
     * @return object Instance of Image
     */
    public function generate()
    {
        $style_settings = $this->style->settings;
        $lines = Text::lines($this->text, $this->style);

        // Create base image
        $width = 0;
        if ($style_settings[Style::KEY_WIDTH]) {
            $width = $style_settings[Style::KEY_WIDTH];
        } else {
            foreach ($lines as $num_line => $line_data) {
                $width = ($line_data[Style::KEY_WIDTH] > $width)
                    ? ($line_data[Style::KEY_WIDTH])
                    : $width;
            }
            $width += $style_settings[Style::KEY_PADDING_LEFT] + $style_settings[Style::KEY_PADDING_RIGHT];
        }

        if ($style_settings[Style::KEY_HEIGHT]) {
            $height = $style_settings[Style::KEY_HEIGHT];
        } else {
            $height = (
                ($style_settings[Style::KEY_LINE_HEIGHT] * $style_settings[Style::KEY_FONT_SIZE] * count($lines))
                + $style_settings[Style::KEY_PADDING_TOP]
                + $style_settings[Style::KEY_PADDING_BOTTOM]
            );
        }

        $this->image = imagecreatetruecolor($width, $height);
        $this->drawBackground();
        $this->drawLines($lines);

        return $this;
    }

    /**
     * Set text
     * @param string $text
     * @return object Instance of Image
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Set style
     * @param object Instance of Style
     * @return object Instance of Image
     */
    public function setStyle($style)
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Draw image background
     * @return object Instance of Image
     */
    public function drawBackground()
    {
        // Transparent background, semi-transparent, or normal
        if (
            !$this->style->settings[Style::KEY_BACKGROUND_COLOR]
            || ($this->style->settings[Style::KEY_BACKGROUND_COLOR] && in_array($this->style->settings[Style::KEY_BACKGROUND_COLOR], ['none', 'transparent']))
        ) {
            if ($this->style->settings[Style::KEY_TYPE] === 'png') {
                imagesavealpha($this->image, true);
                imagealphablending($this->image, false);
                $num_transparent = imagecolorallocatealpha($this->image, 255, 10, 10, 127);
                imagefill($this->image, 0, 0, $num_transparent);
                imagealphablending($this->image, true);
            } elseif ($this->style->settings[Style::KEY_TYPE] === 'gif') {
                // Make transparent color a slight offset from $this->style->settings[Style::KEY_COLOR]
                $transparent_rgb = Color::hexToRgb($this->style->settings[Style::KEY_COLOR]);
                foreach ($transparent_rgb as $color_key => $num_color) {
                    $transparent_rgb[$color_key] = ($num_color === 0) ? $num_color + 1 : $num_color - 1;
                }
                $num_transparent = imagecolorallocate($this->image, $transparent_rgb['r'], $transparent_rgb['g'], $transparent_rgb['b']);
                imagecolortransparent($this->image, $num_transparent);
                imagefill($this->image, 0, 0, $num_transparent);
            }
        } elseif (
            $this->style->settings[Style::KEY_BACKGROUND_COLOR]
            && $this->style->settings[Style::KEY_BACKGROUND_OPACITY] < 100
        ) {
            imagesavealpha($this->image, true);
            imagealphablending($this->image, false);
            $rgb = Color::hexToRgb($this->style->settings[Style::KEY_BACKGROUND_COLOR]);
            $num_opacity = round(127 * ((100-$this->style->settings[Style::KEY_BACKGROUND_OPACITY]) / 100));
            $num_transparent = imagecolorallocatealpha($this->image, $rgb['r'], $rgb['g'], $rgb['b'], $num_opacity);
            imagefill($this->image, 0, 0, $num_transparent);
            imagealphablending($this->image, true);
        } elseif (
            $this->style->settings[Style::KEY_BACKGROUND_COLOR]
            && !in_array($this->style->settings[Style::KEY_BACKGROUND_COLOR], ['none', 'transparent'])
        ) {
            $rgb = Color::hexToRgb($this->style->settings[Style::KEY_BACKGROUND_COLOR]);
            imagefilledrectangle(
                $this->image,
                0,
                0,
                imagesx($this->image),
                imagesy($this->image),
                imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b'])
            );
        }

        if ($this->style->settings[Style::KEY_BACKGROUND_IMAGE]) {
            $this->drawBackgroundImage();
        }
        return $this;
    }


    /**
     * Draw background image
     * @return object Instance of Image
     */
    public static function drawBackgroundImage()
    {
        $background_image_resource = self::getImage($this->style->settings[Style::KEY_BACKGROUND_IMAGE]);
        if (!$background_image_resource) {
            return $this->image;
        }

        // Src is the background-image
        // Dest is the dimage output image
        $src_width = imagesx($background_image_resource);
        $src_height = imagesy($background_image_resource);
        $dest_width = imagesx($this->image);
        $dest_height = imagesy($this->image);

        // A percentage X aligns the point X% across (for horizontal)
        // or down (for vertical) the image with the point X% across (for horizontal)
        // or down (for vertical) the element's padding box.
        // See: http://www.w3.org/TR/CSS2/colors.html#background-properties
        $dest_x = (int) $this->style->settings[Style::KEY_BACKGROUND_POSITION_X];
        if (preg_match('/%$/', $this->style->settings[Style::KEY_BACKGROUND_POSITION_X])) {
            $background_image_x = round(($this->style->settings[Style::KEY_BACKGROUND_POSITION_X] / 100) * $src_width);
            $image_x = round(($this->style->settings[Style::KEY_BACKGROUND_POSITION_X] / 100) * $dest_width);
            $dest_x = $image_x - $background_image_x;
        }

        $dest_y = (int) $this->style->settings[Style::KEY_BACKGROUND_POSITION_Y];
        if (preg_match('/%$/', $this->style->settings[Style::KEY_BACKGROUND_POSITION_Y])) {
            $background_image_y = round(($this->style->settings[Style::KEY_BACKGROUND_POSITION_Y] / 100) * $src_height);
            $image_y = round(($this->style->settings[Style::KEY_BACKGROUND_POSITION_Y] / 100) * $dest_height);
            $dest_y = $image_y - $background_image_y;
        }

        $src_x = 0;
        $src_y = 0;

        imagecopy(
            $this->image,
            $background_image_resource,
            $dest_x,
            $dest_y,
            $src_x,
            $src_y,
            $src_width,
            $src_height
        );

        // repeat x
        $dests_x = [];
        if (in_array($this->style->settings[Style::KEY_BACKGROUND_REPEAT], ['repeat', 'repeat-x'])) {
            // left
            $curr_dest_x = $dest_x;
            while($curr_dest_x >= 0 - $src_width) {
                $curr_dest_x -= $src_width;
                $dests_x[] = $curr_dest_x;
                imagecopy(
                    $this->image,
                    $background_image_resource,
                    $curr_dest_x,
                    $dest_y,
                    $src_x,
                    $src_y,
                    $src_width,
                    $src_height
                );
            }

            // right
            $curr_dest_x = $dest_x;
            while($curr_dest_x <= $dest_width + $src_width) {
                $curr_dest_x += $src_width;
                $dests_x[] = $curr_dest_x;
                imagecopy(
                    $this->image,
                    $background_image_resource,
                    $curr_dest_x,
                    $dest_y,
                    $src_x,
                    $src_y,
                    $src_width,
                    $src_height
                );
            }
        }

        // repeat y
        $dests_y = [];
        if (in_array($this->style->settings[Style::KEY_BACKGROUND_REPEAT], ['repeat', 'repeat-y'])) {
            // above
            $curr_dest_y = $dest_y;
            while($curr_dest_y >= 0 - $src_height) {
                $curr_dest_y -= $src_height;
                $dests_y[] = $curr_dest_y;
                imagecopy(
                    $this->image,
                    $background_image_resource,
                    $dest_x,
                    $curr_dest_y,
                    $src_x,
                    $src_y,
                    $src_width,
                    $src_height
                );
            }

            // below
            $curr_dest_y = $dest_y;
            while($curr_dest_y <= $dest_height + $src_height) {
                $curr_dest_y += $src_height;
                $dests_y[] = $curr_dest_y;
                imagecopy(
                    $this->image,
                    $background_image_resource,
                    $dest_x,
                    $curr_dest_y,
                    $src_x,
                    $src_y,
                    $src_width,
                    $src_height
                );
            }
        }

        // repeat
        // Without this loop, your tiled image will look like a plus sign
        if ($this->style->settings[Style::KEY_BACKGROUND_REPEAT] === 'repeat') {
            if (is_array($dests_x) && count($dests_x) > 0 && is_array($dests_y) && count($dests_y) > 0) {
                foreach ($dests_x as $curr_dest_x) {
                    foreach ($dests_y as $curr_dest_y) {
                        imagecopy(
                            $this->image,
                            $background_image_resource,
                            $curr_dest_x,
                            $curr_dest_y,
                            $src_x,
                            $src_y,
                            $src_width,
                            $src_height
                        );
                    }
                }
            }
        }

        return $this->image;
    }


    /**
     * Draw lines of text
     * @param array $lines
     * @return object Instance of Image
     */
    public function drawLines($lines)
    {
        $rgb = Color::hexToRgb($this->style->settings[Style::KEY_COLOR]);

        // Set starting vertical position
        $num_lines = count($lines);
        $num_line_spacing = (($this->style->settings[Style::KEY_LINE_HEIGHT] - 1) / 2) + 1;
        $num_lines_height = $num_lines * $this->style->settings[Style::KEY_FONT_SIZE] * $this->style->settings[Style::KEY_LINE_HEIGHT] * $num_line_spacing;
        $y = 0;
        switch ($this->style->settings[Style::KEY_VERTICAL_ALIGN]) {
            case 'top':
                $y = $this->style->settings[Style::KEY_PADDING_TOP];
                break;
            case 'middle':
                $y = round((imagesy($this->image) - $num_lines_height + $this->style->settings[Style::KEY_PADDING_TOP] - $this->style->settings[Style::KEY_PADDING_BOTTOM]) / 2);
                break;
            case 'bottom':
                $y = imagesy($this->image) - $num_lines_height - $this->style->settings[Style::KEY_PADDING_BOTTOM];
                break;
        }

        foreach ($lines as $num_line => $line_data) {
            $y += ($this->style->settings[Style::KEY_FONT_SIZE] * $this->style->settings[Style::KEY_LINE_HEIGHT]);
            if ($this->style->settings[Style::KEY_LETTER_SPACING] == 0) {
                $this->drawLine($line_data, $y);
            } else {
                $this->drawLineByChars($line_data, $y);
            }
        }
        imageantialias($this->image, true);

        return $this->image;
    }


    /**
     * Draw a single line of text
     * @param array $lines Line array (text, width)
     * @param integer $y Vertical position of line
     * @return object Instance of Image
     */
    public function drawLine($lines, $y)
    {
        $line_data = $lines;
        $rgb = Color::hexToRgb($this->style->settings[Style::KEY_COLOR]);

        $x = $this->style->settings[Style::KEY_PADDING_LEFT];
        if ($this->style->settings[Style::KEY_TEXT_ALIGN] === 'center') {
            $x = round((imagesx($this->image) / 2) - ($line_data[Style::KEY_WIDTH] / 2));
        } elseif ($this->style->settings[Style::KEY_TEXT_ALIGN] === 'right') {
            $x = imagesx($this->image) - $line_data[Style::KEY_WIDTH] + $this->style->settings[Style::KEY_PADDING_LEFT] - $this->style->settings[Style::KEY_PADDING_RIGHT];
        }

        imagettftext(
            $this->image,
            $this->style->settings[Style::KEY_FONT_SIZE],
            0,
            $x,
            $y,
            imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']),
            $this->style->fontPath($this->style->settings[Style::KEY_FONT_FAMILY]),
            $line_data[Style::KEY_TEXT]
        );

        return $this->image;
    }


    /**
     * Draw a single line of text char by char
     * @param array $lines Line array (text, width)
     * @param integer $y Vertical position of line
     * @return object Instance of Image
     */
    public function drawLineByChars($lines, $y)
    {
        $line_data = $lines;
        $rgb = Color::hexToRgb($this->style->settings[Style::KEY_COLOR]);
        $chars = str_split($line_data[Style::KEY_TEXT]);
        $num_chars = count($chars);

        // Dummy text width.
        // If writing a string longer than 2 chars,
        // imagettfbbox adds space between the chars.
        // This results in the width of 'xy' not being the same as 'x' + 'y'.
        // So we can't just draw char by char and sum the widths,
        // We need to simulate a longer string for width calculations,
        // which is why we use a dummy character.
        $dummy_char = 'x';
        $dummy_char_bounds = imagettfbbox(
            $this->style->settings[Style::KEY_FONT_SIZE],
            0,
            $this->style->fontPath($this->style->settings[Style::KEY_FONT_FAMILY]),
            $dummy_char
        );
        $dummy_char_width = $dummy_char_bounds[2] - $dummy_char_bounds[0];

        $x = $this->style->settings[Style::KEY_PADDING_LEFT];
        if (in_array($this->style->settings[Style::KEY_TEXT_ALIGN] , ['center', 'right'])) {
            $num_line_width = 0;
            foreach ($chars as $num_char => $char) {
                $current_char_bounds = imagettfbbox(
                    $this->style->settings[Style::KEY_FONT_SIZE],
                    0,
                    $this->style->fontPath($this->style->settings[Style::KEY_FONT_FAMILY]),
                    $char.$dummy_char
                );
                $current_char_width = $current_char_bounds[2] - $current_char_bounds[0] - $dummy_char_width;
                $num_line_width += $current_char_width;
                if ($num_char < $num_chars - 1) {
                    $num_line_width += $this->style->settings[Style::KEY_LETTER_SPACING];
                }
            }
            if ($this->style->settings[Style::KEY_TEXT_ALIGN] === 'center') {
                $x = round((imagesx($this->image) / 2) - ($num_line_width / 2));
            } elseif ($this->style->settings[Style::KEY_TEXT_ALIGN] === 'right') {
                $x = $this->style->settings[Style::KEY_WIDTH] - $num_line_width - $this->style->settings[Style::KEY_PADDING_RIGHT];
            }
        }

        // Draw char by char
        foreach ($chars as $num_char => $char) {
            imagettftext(
                $this->image,
                $this->style->settings[Style::KEY_FONT_SIZE],
                0,
                $x,
                $y,
                imagecolorallocate($this->image, $rgb['r'], $rgb['g'], $rgb['b']),
                $this->style->fontPath($this->style->settings[Style::KEY_FONT_FAMILY]),
                $char
            );
            $current_char_bounds = imagettfbbox(
                $this->style->settings[Style::KEY_FONT_SIZE],
                0,
                $this->style->fontPath($this->style->settings[Style::KEY_FONT_FAMILY]),
                $char.$dummy_char
            );
            $current_char_width = $current_char_bounds[2] - $current_char_bounds[0] - $dummy_char_width;
            $x += $current_char_width;
            if ($num_char < $num_chars - 1) {
                $x += $this->style->settings[Style::KEY_LETTER_SPACING];
            }
        }
        return $this->image;
    }

    /**
     * Get an image by URL
     * @param string $url
     * @return resource Image resource
     */
    public static function getImage($url)
    {
        $path_info = pathinfo($url);
        if (!in_array($path_info['extension'], ['png', 'gif', 'jpg', 'jpeg'])) {
            return false;
        }

        if (!preg_match('/^http/', $url)) {
            $protocol_segments = explode('/', $_SERVER['SERVER_PROTOCOL']);
            $url = strtolower($protocol_segments[0].'://'.$_SERVER['HTTP_HOST']).$url;
        }

        $curl_options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_BINARYTRANSFER => TRUE,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => __CLASS__.' PHP library',
        ];
        $curl_resource = curl_init();
        curl_setopt_array($curl_resource, $curl_options);
        $data = curl_exec($curl_resource);
        $curl_info = curl_getinfo($curl_resource);
        curl_close($curl_resource);
        if (!$data || !preg_match('/^2/', $curl_info['http_code'])) {
            return false;
        }

        $image_resource = imagecreatefromstring($data);
        return $image_resource;
    }
}
