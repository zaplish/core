<?php

namespace App\Helpers;

class ArrayHelper
{
    /**
     * Recursively merge arrays (deep merge).
     *
     * - Keeps existing values unless overwritten
     * - Merges nested arrays
     * - Overwrites scalar values
     *
     * @param array $array1 Existing array
     * @param array $array2 Incoming array (overwrites/extends)
     * @return array
     */
    public static function mergeRecursiveDistinct(?array $array1, ?array $array2): array
    {
        $array1 = $array1 ?? [];
        $array2 = $array2 ?? [];

        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::mergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Recursively remove values from an array by path.
     *
     * @param array|null $array  Input array
     * @param array|null $path   Path of keys to remove (can be nested)
     * @return array
     */
    public static function removeRecursive(?array $array, ?array $path): array
    {
        $array = $array ?? [];
        $path  = $path ?? [];

        foreach ($path as $key => $value) {
            if (is_array($value)) {
                // Go deeper if $value is an array
                if (isset($array[$key]) && is_array($array[$key])) {
                    $array[$key] = self::removeRecursive($array[$key], $value);
                }
            } else {
                // If $key is numeric, we’re just given a key name in $value
                $targetKey = is_int($key) ? $value : $key;

                if (isset($array[$targetKey])) {
                    unset($array[$targetKey]);
                }
            }
        }

        return $array;
    }
}
