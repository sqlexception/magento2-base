<?php declare(strict_types=1);

namespace SqlException\Base\Helper;

/**
 * Helper class for generating UUIDs and random strings.
 */
class UUIDHelper
{
    /**
     * Generates a random UUID.
     *
     * @return string
     */
    public static function generateUuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Generates a random string of a given length.
     *
     * @param int $length
     * @return string
     */
    public static function generateRandomString(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);

        // Ensures the second argument to str_repeat is an integer
        return substr(str_shuffle(str_repeat($characters, max(1, (int) ceil($length / $charactersLength)))), 0, $length);
    }
}
