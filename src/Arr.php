<?php
declare(strict_types=1);

namespace PHRETS;

/**
 * Convenience functions for working with arrays
 * This is to avoid dependencies on external libraries
 *
 */
class Arr
{
    /**
     * Gets elements from the array with dot notation support
     *
     * Lifted from: https://medium.com/@assertchris/dot-notation-3fd3e42edc61
    */
    public static function get(array $array, string $key): mixed
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        $parts = explode('.', $key);
        
        foreach ($parts as $part) {
            if (!is_array($array) || !array_key_exists($part, $array)) {
                return null;
            }

            $array = $array[$part];
        }
        
        return $array;
    }

    /**
     * @template T
     * @param array<int|string,T> $values
     * @return T|null
     */
    public static function first(array $values): mixed
    {
        $key = array_key_first($values);
        if ($key === null) {
            return null;
        }

        return $values[$key];
    }

     /**
     * @template T
     * @param array<int|string,T> $values
     * @return T|null
     */
    public static function last(array $values): mixed
    {
        $key = array_key_last($values);
        if ($key === null) {
            return null;
        }

        return $values[$key];
    }
}
