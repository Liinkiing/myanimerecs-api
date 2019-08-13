<?php

if (!function_exists('array_flatten')) {
    /**
     * Convert a multi-dimensional array into a single-dimensional array.
     * @param array $array The multi-dimensional array.
     * @return array|bool
     * @author Sean Cannon, LitmusBox.com | seanc@litmusbox.com
     */
    function array_flatten(array $array)
    {
        if (!is_array($array)) {
            return false;
        }
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, array_flatten($value));
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}

if (!function_exists('array_sort_by_occurrences')) {
    function array_sort_by_occurrences(array $array, int $min): array {
        $occurrences = array_count_values($array);
        arsort($occurrences);
        $result = [];

        foreach($occurrences as $key=>$val){
            if ($val < $min) {
                continue;
            }
            for($i=0;$i<$val;$i++){
                $result[] = $key;
            }
        }

        return $result;
    }
}
