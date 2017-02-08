<?php

namespace DImage;

class Text
{
    /**
     * Sanitize the text
     * @param string $text
     * @return string
     */
    public static function sanitize($text)
    {
        $replacements = [
            '/1amp1/i'   => '&',
            '/’/'        => '\'',
            '/_/'        => '',
            '/“|”/'      => '"',
            '/\n|\r\|\t/'=> ''
        ];
        return preg_replace(
            array_keys($replacements),
            array_values($replacements),
            $text
        );
    }

    /**
     * Transform the text to appropriate case
     * @param string $text
     * @param string $transform
     * @return string
     */
    public static function transform($text, $transform)
    {
        switch ($transform)
        {
            case 'uppercase':
                return strtoupper($text);
            case 'lowercase':
                return strtolower($text);
            case 'capitalize':
                $segments = explode(' ', $text);
                return join(' ', array_map('ucwords', $segments));
        }
        return $text;
    }

    /**
     * Get text lines, splitting source text as necessary
     * @param mixed $text String of text or Array of texts
     * @param object $style Instace of DImage\Style
     * @return array Lines
     */
    public static function lines($text, $style)
    {
        $lines = [];

        // Text array
        if (is_array($text)) {
            foreach ($text as $line) {
                $lines = array_merge($lines, self::lines($line, $style));
            }
            return $lines;
        }

        // NEW Hack
        $style_settings = $style->settings;

        // Text string
        $words = explode(' ', $text);
        $words = array_filter($words, 'strlen');
        if (!is_array($words)) {
            return [];
        }

        $num_words = count($words);

        $line_container_width = $style_settings[Style::KEY_MAX_WIDTH] - $style_settings[Style::KEY_PADDING_LEFT] - $style_settings[Style::KEY_PADDING_RIGHT];
        $current_line_words = [];
        foreach ($words as $num_word => $word) {
            $word = Text::sanitize($word);
            $word = Text::transform($word, $style_settings[Style::KEY_TEXT_TRANSFORM]);

            // Will adding this word fit on the current line?
            $line = join(' ', array_merge($current_line_words, [$word]));
            $current_bounds = imagettfbbox(
                $style_settings[Style::KEY_FONT_SIZE],
                0,
                $style->fontPath($style_settings[Style::KEY_FONT_FAMILY]),
                $line
            );
            $letter_spacing = $style_settings[Style::KEY_LETTER_SPACING] * (strlen($line) - 1);
            if (($current_bounds[2] - $current_bounds[0] + $letter_spacing) < $line_container_width) {
                $current_line_words[] = $word;
                if ($num_word === $num_words - 1) {
                    $lines[] = [
                        Style::KEY_WIDTH => $current_bounds[2] - $current_bounds[0] + $letter_spacing,
                        Style::KEY_TEXT => join(' ', $current_line_words)
                    ];
                    return $lines;
                }
                continue;
            }

            // Does this word fit on a single line by itself? If not, hyphenate it.
            if (count($current_line_words) === 0) {
                $word_bounds = imagettfbbox(
                    $style_settings[Style::KEY_FONT_SIZE],
                    0,
                    $style->fontPath($style_settings[Style::KEY_FONT_FAMILY]),
                    $word
                );
                $letter_spacing = round($style_settings[Style::KEY_LETTER_SPACING] * (strlen($word) - 1));
                if (($word_bounds[2] - $word_bounds[0] + $letter_spacing) < $line_container_width) {
                    $line_text = trim(join(' ', $current_line_words));
                    if (strlen($line_text) > 0) {
                        $lines[] = [
                            Style::KEY_WIDTH => $word_bounds[2] - $word_bounds[0] + $letter_spacing,
                            Style::KEY_TEXT => $line_text,
                        ];
                        $current_line_words = [$word];
                    }
                    continue;
                }

                // Hyphenate word as necessary
                $lines = array_merge(
                    $lines,
                    self::hyphenatedLines($word, $style_settings)
                );

                continue;
            }

            // Output the current line and start a new one
            $line_text = trim(join(' ', $current_line_words));
            if (strlen($line_text) > 0) {
                $current_bounds = imagettfbbox(
                    $style_settings[Style::KEY_FONT_SIZE],
                    0,
                    $style->fontPath($style_settings[Style::KEY_FONT_FAMILY]),
                    $line_text
                );
                $letter_spacing = round($style_settings[Style::KEY_LETTER_SPACING] * (strlen($line_text) - 1));
                $lines[] = [
                    Style::KEY_WIDTH => $current_bounds[2] - $current_bounds[0] + $letter_spacing,
                    Style::KEY_TEXT => $line_text
                ];
            }
            $current_line_words = [$word];
        }

        if (count($current_line_words) > 0) {
            $word_bounds = imagettfbbox(
                $style_settings[Style::KEY_FONT_SIZE],
                0,
                $style->fontPath($style_settings[Style::KEY_FONT_FAMILY]),
                $word
            );
            $lines[] = [
                Style::KEY_WIDTH => $word_bounds[2] - $word_bounds[0],
                Style::KEY_TEXT => $word
            ];
        }

        return $lines;
    }

    /**
     * Get hyphenated lines
     * @param mixed $text String of text or Array of texts
     * @param object $style Instace of DImage\Style
     * @return array
     */
    public static function hyphenatedLines($text, $style)
    {
        $style_settings = $style->settings;

        $chars = str_split($text);
        if (!is_array($chars)) {
            return [];
        }

        $num_chars = count($chars);
        $line_chars = [];
        $hyphenated_lines = [];
        foreach ($chars as $num_char => $char) {
            $line = join('', $line_chars).$char.'-';
            $line_char_bounds = imagettfbbox(
                $style_settings[Style::KEY_FONT_SIZE],
                0,
                $style->fontPath($style_settings[Style::KEY_FONT_FAMILY]),
                $line
            );
            $letter_spacing = round($style_settings[Style::KEY_LETTER_SPACING] * (strlen($line) - 1));
            if (($line_char_bounds[2] - $line_char_bounds[0] + $letter_spacing) < ($style_settings[Style::KEY_MAX_WIDTH] - $style_settings[Style::KEY_PADDING_LEFT] - $style_settings[Style::KEY_PADDING_RIGHT])) {
                $line_chars[] = $char;
                if ($num_char === $num_chars - 1) {
                    $hyphenated_lines[] = array(
                        Style::KEY_WIDTH => $line_char_bounds[2] - $line_char_bounds[0] + $letter_spacing,
                        Style::KEY_TEXT => join('', array_merge($line_chars))
                    );
                }
                continue;
            }

            $hyphenated_lines[] = array(
                Style::KEY_WIDTH => $line_char_bounds[2] - $line_char_bounds[0] + $letter_spacing,
                Style::KEY_TEXT => join('', $line_chars).$char.(($char !== '-' && ($num_char < $num_chars - 1)) ? '-' : '')
            );

            $line_chars = [];
        }

        // If the previous line was hyphenated, don't start this line with a hyphen
        // Comes into play when hyphenated strings get split
        // Ex: "Jane Doe-Smith" should be split as "Jane Doe-"+"Smith", not "Jane Doe-"+"-"Smith"
        if (is_array($hyphenated_lines) && count($hyphenated_lines) > 0) {
            foreach ($hyphenated_lines as $num_hyphenated_line => $hyphenated_line_data) {
                if (
                    array_key_exists($num_hyphenated_line - 1, $hyphenated_lines)
                    && preg_match('/[-]{1}$/', $hyphenated_lines[$num_hyphenated_line-1][Style::KEY_TEXT])
                ) {
                    $hyphenated_lines[$num_hyphenated_line][Style::KEY_TEXT] = preg_replace('/^[-]{1}/', '', $hyphenated_line_data[Style::KEY_TEXT]);
                }
            }
        }

        return $hyphenated_lines;
    }
}