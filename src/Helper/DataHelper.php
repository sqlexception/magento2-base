<?php declare(strict_types=1);

namespace SqlException\Base\Helper;

/**
 * Helper class for manipulating data structures like arrays.
 */
class DataHelper
{
    /**
     * Replaces a value in a specified column of a multi-dimensional array.
     *
     * @param mixed $search
     * @param mixed $replace
     * @param string $column
     * @param array $inputData
     * @return array
     */
    public static function replaceStringInColumn($search, $replace, string $column, array $inputData): array
    {
        return array_map(function ($row) use ($search, $replace, $column) {
            if (isset($row[$column])) {
                $row[$column] = str_replace($search, $replace, $row[$column]);
            }
            return $row;
        }, $inputData);
    }
}
