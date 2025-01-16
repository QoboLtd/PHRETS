<?php
declare(strict_types=1);

/*
The MIT License (MIT)

Copyright (c) Taylor Otwell

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit
persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

The license is due to copied and modified code from Illuminate collections Arr.php source file to avoid dependencies
on external libraries.
The copied code are the functions: accessible, exists and get.

The changes to the code are:
1. Introduce native types.
2. Remove functionality we don't' need such as defaults other than null.
*/
namespace PHRETS;

use ArrayAccess;

/**

 *
 */
class Arr
{
    public static function accessible(mixed $value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param  \ArrayAccess|array<int|string,mixed>  $array
     * @param  string|int|float  $key
     */
    public static function exists(ArrayAccess|array $array, string|int|float $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        if (is_float($key)) {
            $key = (string) $key;
        }

        return array_key_exists($key, $array);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array<int|string,mixed>  $array
     */
    public static function get(ArrayAccess|array $array, string|int|null $key): mixed
    {
        if (!self::accessible($array)) {
            return null;
        }

        if ($key === null) {
            return $array;
        }

        if (self::exists($array, $key)) {
            return $array[$key];
        }

        if (!is_string($key) || !str_contains($key, '.')) {
            return $array[$key] ?? null;
        }

        foreach (explode('.', $key) as $segment) {
            if (self::accessible($array) && self::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return null;
            }
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
