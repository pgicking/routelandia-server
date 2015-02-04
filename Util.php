<?php

class Util
{
    /** Check if array has a next element
     *
     * check if an array has a next element
     * NOTE: Placed here because I dont know where "misc" functions should
     * go in our current MVC set up. I expect this needs to be moved - Peter
     * TODO: Move this function somewhere more appropriate
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
