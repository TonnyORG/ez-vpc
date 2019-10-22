<?php

namespace EzVpc\Tools\Helpers;

class Str
{
    /**
     * Determines if the given $haystack starts with $needle.
     *
     * @param string $haystack
     * @param string $needle
     * @return boolean
     */
    public static function startsWith(string $haystack, string $needle)
    {
        return (substr($haystack, 0, strlen($needle)) === $needle);
    }

    /**
     * Determines if the given $haystack ends with $needle.
     *
     * @param string $haystack
     * @param string $needles
     * @return boolean
     */
    public static function endsWith(string $haystack, string $needle)
    {
        $length = strlen($needle);

        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
