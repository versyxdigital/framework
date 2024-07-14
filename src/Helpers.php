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

if (!function_exists('appRootDir')) {
    function appRootDir() {
        $currentDir = __DIR__;

        while (true) {
            if (file_exists($currentDir . '/composer.json') && is_dir($currentDir . '/vendor')) {
                if (basename(dirname($currentDir)) === 'vendor') {
                    $currentDir = dirname($currentDir);
                    continue;
                }
                break;
            }

            $parentDir = dirname($currentDir);

            if ($parentDir === $currentDir) {
                throw new Exception('Unable to locate the application root');
            }

            $currentDir = $parentDir;
        }

        $projectRoot = $currentDir;

        return $projectRoot;
    }
}

if (!function_exists('extractObject')) {
    function extractObject (&$array, string $column, string $key)
    {
        $key = array_search($key, array_column($array, $column), true);

        if (! $key) {
            return null; // No object with key found
        }

        // Extract the current object
        $current = $array[$key];

        // Unset it from the array
        unset($array[$key]);

        // Reindex the array
        $array = array_values($array);

        return $current;
    }
}

if (!function_exists('generateSecureSessionId')) {
    function generateSecureSessionId($length = 32) {
        return bin2hex(random_bytes($length));
    }
}