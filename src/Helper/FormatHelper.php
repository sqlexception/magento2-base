<?php declare(strict_types=1);

namespace SqlException\Base\Helper;

namespace SqlException\Base\Helper;

/**
 * Helper class for common format and conversion operations.
 */
class FormatHelper
{
    /**
     * Converts camelCase to snake_case.
     *
     * @param string $input
     * @return string
     */
    public static function camelCaseToSnakeCase(string $input): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));
    }

    /**
     * Formats a string by replacing placeholders with the given values.
     *
     * @param string $template
     * @param array $placeholders
     * @return string
     */
    public static function formatString(string $template, array $placeholders): string
    {
        return strtr($template, $placeholders);
    }
}
