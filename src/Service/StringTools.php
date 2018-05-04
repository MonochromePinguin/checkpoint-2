<?php

namespace Service;

/**
 *  class StringTools
 */
class StringTools
{

    /**
     * Simulate the basic funtionality of ltrim() function
     * @param string $str the string to trim
     * @return string
     */
    public static function trimWhiteSpaces(string $str):string
    {
        $p = 0;
        while (in_array($str[$p++], [' ', ' ', "\t", "\n", "\v"])) {
        }
        echo \substr($str, $p);
        return \substr($str, $p -1);
    }
}
