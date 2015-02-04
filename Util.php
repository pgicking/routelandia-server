<?php


class Util
{
    /** Check if array has a next element
     *
     * check if an array has a next element
     *
     * @param array $array
     * @return bool
     */
    static function has_next($array)
    {
        if (is_array($array)) {
            if (next($array) === false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
}
