<?php

namespace UseLocale\LocaleLaravel\Support\Utils;

/**
 * Flatten a multi-dimensional associative array with dots.
 * https://github.com/laravel/framework/blob/c61fd7aa262476e9f756eff84b0de43e1f4406e8/src/Illuminate/Collections/Arr.php#L109
 *
 * @param  iterable  $array
 * @param  string  $prepend
 * @return array
 */
function array_dot($array, $prepend = '')
{
    $results = [];

    foreach ($array as $key => $value) {
        if (is_array($value) && ! empty($value)) {
            $results = array_merge($results, array_dot($value, $prepend.$key.'.'));
        } else {
            $results[$prepend.$key] = $value;
        }
    }

    return $results;
}

/**
 * Convert a flatten "dot" notation array into an expanded array.
 * https://github.com/laravel/framework/blob/c61fd7aa262476e9f756eff84b0de43e1f4406e8/src/Illuminate/Collections/Arr.php#L130
 *
 * @param  iterable  $array
 * @return array
 */
function array_undot($array)
{
    $results = [];

    foreach ($array as $key => $value) {
        array_dot_set($results, $key, $value);
    }

    return $results;
}

/**
 * Set an array item to a given value using "dot" notation.
 *
 * If no key is given to the method, the entire array will be replaced.
 * https://github.com/laravel/framework/blob/c61fd7aa262476e9f756eff84b0de43e1f4406e8/src/Illuminate/Collections/Arr.php#L667
 *
 * @param  array  $array
 * @param  string|int|null  $key
 * @param  mixed  $value
 * @return array
 */
function array_dot_set(&$array, $key, $value)
{
    if (is_null($key)) {
        return $array = $value;
    }

    $keys = explode('.', $key);

    foreach ($keys as $i => $key) {
        if (count($keys) === 1) {
            break;
        }

        unset($keys[$i]);

        // If the key doesn't exist at this depth, we will just create an empty array
        // to hold the next value, allowing us to create the arrays to hold final
        // values at the correct depth. Then we'll keep digging into the array.
        if (! isset($array[$key]) || ! is_array($array[$key])) {
            $array[$key] = [];
        }

        $array = &$array[$key];
    }

    $array[array_shift($keys)] = $value;

    return $array;
}
