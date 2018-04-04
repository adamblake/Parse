<?php

/**
 * Ini parser class.
 *
 * @author Adam Blake <theadamattack@gmail.com>
 * @copyright (C) 2016 Adam Blake <theadamattack@gmail.com>
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

namespace adamblake\parse\Parser;

use adamblake\parse\ParseException;

/**
 * Parses INI strings to array.
 *
 * @author Adam Blake <theadamattack@gmail.com>
 * @copyright (C) 2016 Adam Blake <theadamattack@gmail.com>
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
class Ini implements ParserInterface
{
    /**
     * {@inheritdoc}
     *
     * @param string $string The string of data to parse.
     *
     * @return array The parsed data.
     * @throws ParseException if the string is invalid INI.
     */
    public static function parse(string $string): array
    {
        $ini = self::parseIniString(trim($string), true) ?? [];
        $unpacked = self::unpackNestedKeys($ini);

        return $unpacked;
    }

    /**
     * Wrapper for parse_ini_string that throws ParseExceptions rather than
     * returning false and throwing a warning.
     *
     * @return array The parsed INI string.
     * @throws ParseException if the string is invalid INI.
     *
     * @see \parse_ini_string()
     */
    public static function parseIniString(): array
    {
        set_error_handler(ParseException::class . '::errorHandler');
        $ini = call_user_func_array('parse_ini_string', func_get_args());
        restore_error_handler();

        return $ini;
    }

    /**
     * Unpacks nested INI sections/arrays.
     *
     * @param array $ini The INI array with keys to unpack.
     *
     * @return array The unpacked array of data.
     */
    private static function unpackNestedKeys(array $ini): array
    {
        foreach ($ini as $key => $value) {
            if (strpos($key, '.') !== false) {
                $ini = array_merge_recursive($ini, self::nest($key, $value));
                unset($ini[$key]);
            }
        }
        
        return $ini;
    }
    
    /**
     * Nests a value deeply according to the keys specified in the dot string.
     * 
     * The keys for each nested array should be specified in the dot string
     * delimited by dots ('.'). For example, 'name.first' should yield a nested
     * array of ['name' => ['first' => $value]] where $value is the value passed
     * as the second argument.
     * 
     * @param string $dotString The string specifying the nested structure.
     * @param mixed  $value     The value assigned to the deepest key.
     * 
     * @return array The nested array with the value stored at the deepest key.
     */
    private static function nest(string $dotString, $value): array
    {
        $keys = explode('.', $dotString);
        $nest = [array_pop($keys) => $value];
        foreach (array_reverse($keys) as $key) {
            $nest = [$key => $nest];
        }
        
        return $nest;
    }
}
