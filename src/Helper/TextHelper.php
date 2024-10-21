<?php declare(strict_types=1);

namespace SqlException\Base\Helper;

/**
 * Helper class for common text manipulation operations.
 */
class TextHelper
{
    /**
     * Truncates a string to the specified length and appends a suffix if truncated.
     *
     * @param string $text
     * @param int $length
     * @param string $append
     * @return string
     */
    public static function truncateString(string $text, int $length, string $append = '...'): string
    {
        if (strlen($text) > $length) {
            return substr($text, 0, $length) . $append;
        }
        return $text;
    }

    /**
     * Converts a string into a slug for SEO-friendly URLs.
     *
     * @param string $text
     * @return string
     */
    public static function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text);
    }
}
