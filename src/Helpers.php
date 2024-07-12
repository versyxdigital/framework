<?php

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param string $key
     * @param string|null $default
     *
     * @return mixed
     */
    function env(string $key, string|null $default = null)
    {
        $value = getenv($key);

        if (! $value) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        if (substr($value, 0, 1) == '"' && substr($value, -1) == '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (!function_exists('toArray')) {
    /**
     * Helper method to convert objects to arrays.
     *
     * @param $object
     *
     * @return array
     */
    function toArray($object): array
    {
        if (!is_object($object) && !is_array($object)) {
            return $object;
        }

        return array_map('toArray', (array) $object);
    }
}
