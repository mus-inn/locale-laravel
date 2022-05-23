<?php

if (! function_exists('lang_path')) {
    /**
     * Get the path to the language folder. For Laravel < 8.
     *
     * @param string $path
     * @return string
     */
    function lang_path($path = '')
    {
        return base_path('resources/lang') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}
