<?php

namespace EzVpc\Tools\Helpers;

class Arr
{
    /**
     * Transforms a given array indexed by numbers and
     * re-index it by then given $key.
     *
     * @param string $key
     * @param array $items
     * @return array
     */
    public static function indexArrayById(string $key, array $items)
    {
        foreach ($items as $index => $item) {
            unset($items[$index]);

            $items[$item[$key]] = $item;
        }

        return $items;
    }

    /**
     * Build an array of values extracted from the given array
     * using $key property.
     *
     * @param string $key
     * @param array $items
     * @return array
     */
    public static function getPropertyValuesByKey(string $key, array $items)
    {
        $values = [];

        foreach ($items as $item) {
            $values[] = $item[$key];
        }

        return $values;
    }
}
