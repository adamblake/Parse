<?php

/**
 * Ini parser class.
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 * @copyright (C) 2016 Adam Blake <adamblake@g.ucla.edu>
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace adamblake\Parse\Parser;

use adamblake\Parse\ParseException;

/**
 * Parses INI strings to array.
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 * @copyright (C) 2016 Adam Blake <adamblake@g.ucla.edu>
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
class Ini implements IParser
{
    /**
     * {@inheritdoc}
     *
     * @param string $string The string of data to parse.
     *
     * @return array The parsed data.
     *
     * @throws adamblake\Parse\ParseException Throws an exception if the string is invalid.
     */
    public static function parse($string)
    {
        $ini = self::parseIniString(trim($string), true);

        if (null === $ini) {
            // empty file
            $ini = [];
        }

        if (!is_array($ini)) {
            // not an array
            throw new ParseException(sprintf('The file "%s" must '
                .'have a valid INI structure.', $string));
        }

        // multidimensional inis
        self::fixIniMulti($ini);

        return $ini;
    }

    /**
     * Wrapper for parse_ini_string that throws ErrorExceptions rather than
     * returning false and throwing a warning.
     *
     * @return string The parsed INI string.
     *
     * @throws ParseException Throws an exception when parse_ini_string cannot
     *                        open the file.
     *
     * @see \parse_ini_string()
     */
    public static function parseIniString()
    {
        set_error_handler('\adamblake\Parse\Parse::errorException');
        $ini = call_user_func_array('parse_ini_string', func_get_args());
        restore_error_handler();

        return $ini;
    }

    /**
     * Unpacks nested INI sections/arrays.
     *
     * @param array $ini_arr The INI array to unpack.
     */
    protected static function fixIniMulti(array &$ini_arr)
    {
        foreach ($ini_arr as $key => &$value) {
            if (is_array($value)) {
                self::fixIniMulti($value);
            }
            if (false !== strpos($key, '.')) {
                $key_arr = explode('.', $key);
                $last_key = array_pop($key_arr);
                $cur_elem = &$ini_arr;
                foreach ($key_arr as $key_step) {
                    $cur_elem[$key_step] = !isset($cur_elem[$key_step]) ? array() : $cur_elem[$key_step];
                    $cur_elem = &$cur_elem[$key_step];
                }
                $cur_elem[$last_key] = $value;
                unset($ini_arr[$key]);
            }
        }
    }
}
