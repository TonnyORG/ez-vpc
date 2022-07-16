<?php

namespace EzVpc\Tools\Helpers;

class Arr
{
    /**
     * Filter a given array of $items, and return a new array
     * with the same items but only containing the given $properties.
     *
     * @param array $properties
     * @param array $items
     * @return array
     */
    public static function filterSelectedProperties(array $properties, array $items)
    {
        return array_map(function($item) use ($properties) {
            if (is_array($item)) {
                $removeKeys = array_diff(array_keys($item), $properties);
                foreach ($removeKeys as $key) {
                    unset($item[$key]);
                }
            } elseif (is_object($item)) {
                $removeKeys = array_diff(get_object_vars($item), $properties);
                foreach ($removeKeys as $key) {
                    unset($item->$key);
                }
            }

            return $item;
        }, $items);
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

    /**
     * Transforms a given array indexed by numbers and
     * re-index it by then given $key.
     *
     * @param string $key
     * @param array $items
     * @return array
     */
    public static function reindexArrayByKey(string $key, array $items)
    {
        foreach ($items as $index => $item) {
            unset($items[$index]);

            $items[$item[$key]] = $item;
        }

        return $items;
    }

    /**
     * Build and return a new array sorted and filtered by the given
     * $headers, the $headers order determine the order of the $row's
     * properties.
     *
     * @param array $headers
     * @param array $rows
     * @return array
     */
    public static function prepareDataForTable(array $headers, array $rows)
    {
        $invertedHeaders = [];
        foreach ($headers as $index => $property) {
            $invertedHeaders[$property] = $index;
        }

        return array_map(function($row) use ($invertedHeaders) {
            $newRow = [];
            if (!is_array($row)) {
                $row = (array) $row;
            }

            foreach ($invertedHeaders as $key => $index) {
                $newRow[$index] = $row[$key];
            }

            return $newRow;
        }, $rows);
    }
}
