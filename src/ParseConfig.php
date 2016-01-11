<?php

/**
 * ParseConfig class.
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 * @copyright (C) 2014 Adam Blake <adamblake@g.ucla.edu>
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

namespace adamblake;

use Symfony\Component\Yaml\Yaml;

/**
 * Collection of static functions for parsing files.
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 * @copyright (C) 2014 Adam Blake <adamblake@g.ucla.edu>
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
class ParseConfig
{
    /**
     * Parses a configuration file and returns an associative array.
     *
     * @param string $filename     The configuration file to parse.
     * @param bool   $returnObject Indicates whether to return an object or array.
     *
     * @return array|\stdClass Associative array or class of the configurations.
     *
     * @throws ParseConfigException An exception is thrown when an unsupported
     *                              file is passed.
     */
    public static function parse($filename, $returnObject = false)
    {
        $ext = strtolower(self::getExt($filename));

        if ('yml' === $ext || 'yaml' === $ext) {
            $config = self::yaml($filename);
        } elseif ('json' === $ext) {
            $config = self::json($filename);
        } elseif ('ini' === $ext) {
            $config = self::ini($filename);
        }

        if (!isset($config)) {
            throw new ParseConfigException('The given config file '
                ."'$filename' is invalid or of an unsupported file type "
                .'(supported: YAML, JSON, INI.');
        }

        if ($returnObject) {
            $config = self::arrayToObject($config);
        }

        return $config;
    }

    /**
     * Parses a YAML file or string.
     *
     * @param string $input    The YAML file path or YAML string.
     * @param bool   $isString Set true for string input instead of file.
     *
     * @return array Associative array of the file.
     *
     * @throws ParseConfigException An exception is thrown when config cannot be
     *                              parsed.
     */
    public static function yaml($input, $isString = false)
    {
        $contents = $isString ? $input : self::fileGetContents($input);
        $yaml = Yaml::parse(trim($contents));

        if (null === $yaml) {
            // empty file
            $yaml = [];
        }

        if (!is_array($yaml)) {
            // not an array
            throw new ParseConfigException(sprintf('The input "%s" must '
                .'contain or be a valid YAML structure.', $input));
        }

        return $yaml;
    }

    /**
     * Parses a JSON file or string.
     *
     * @param string $input    The JSON file path or JSON string.
     * @param bool   $isString Set to true for string input instead of file.
     *
     * @return array Associative array of the file.
     *
     * @throws ParseConfigException An exception is thrown when JSON decode
     *                              cannot parse the file.
     */
    public static function json($input, $isString = false)
    {
        $contents = $isString ? $input : self::fileGetContents($input);
        $json = json_decode(trim($contents), true);
        $error = json_last_error();

        if (null === $json) {
            // empty file
            $json = [];
        }

        if (JSON_ERROR_NONE !== $error) {
            // error occurred
            throw new ParseConfigException(sprintf('Failed to parse JSON '
                ."file '%s', error: '%s'", $input, json_last_error_msg()));
        }

        return $json;
    }

    /**
     * Parses an INI file or string.
     *
     * @param string $input    The INI file path or INI string.
     * @param bool   $isString Switch to true for string input instead of file.
     *
     * @return array Associative array of the file.
     *
     * @throws ParseConfigException An exception is thrown when the INI file
     *                              cannot be parsed.
     */
    public static function ini($input, $isString = false)
    {
        $contents = $isString ? $input : self::fileGetContents($input);
        $ini = self::parseIniString(trim($contents), true);

        if (null === $ini) {
            // empty file
            $ini = [];
        }

        if (!is_array($ini)) {
            // not an array
            throw new ParseConfigException(sprintf('The file "%s" must '
                .'have a valid INI structure.', $input));
        }

        // multidimensional inis
        self::fixIniMulti($ini);

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

    /**
     * Converts an array to an object using stdClass.
     *
     * @param array $array The array to convert.
     *
     * @return \stdClass The object form of the array.
     */
    public static function arrayToObject(array $array)
    {
        $object = new \stdClass();
        foreach ($array as $key => $value) {
            $object->{$key} = $value;
        }

        return $object;
    }

    /**
     * Wrapper for file_get_contents that throws Exceptions, rather than
     * returning false and throwing a warning.
     * Note: only the filename parameter is used in this wrapper's signature,
     * but all given arguments are passed to file_get_contents.
     *
     * @return string The read data.
     *
     * @throws \ParseConfigException Throws an exception when file_get_contents
     *                               cannot open the file.
     *
     * @see \file_get_contents()
     */
    public static function fileGetContents()
    {
        self::setErrorHandler();
        $contents = call_user_func_array('file_get_contents', func_get_args());
        restore_error_handler();

        return $contents;
    }

    /**
     * Wrapper for parse_ini_string that throws ErrorExceptions rather than
     * returning false and throwing a warning.
     *
     * @return string The parsed INI string.
     *
     * @throws \ParseConfigException Throws an exception when file_get_contents
     *                               cannot open the file.
     *
     * @see \parse_ini_string()
     */
    public static function parseIniString()
    {
        self::setErrorHandler();
        $ini = call_user_func_array('parse_ini_string', func_get_args());
        restore_error_handler();

        return $ini;
    }

    /**
     * Sets the error handler.
     *
     * @see \set_error_handler(), ParseConfig::errorException()
     */
    protected static function setErrorHandler()
    {
        set_error_handler(__NAMESPACE__.'\ParseConfig::errorException');
    }

    /**
     * Error handler that throws ErrorExceptions instead of warnings.
     *
     * @param int  $num  The error number.
     * @param type $str  The error string.
     * @param type $file The file the error occurred in.
     * @param type $line The line of the file the error occured in.
     *
     * @return bool Returns false if the error was suppressed.
     *
     * @throws ParseConfigException Errors are redirected through this exception.
     */
    public static function errorException($num, $str, $file, $line)
    {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }

        throw new ParseConfigException($str, 0, $num, $file, $line);
    }

    /**
     * Determines the extension of a given file.
     *
     * @param string $filename The path to the file.
     *
     * @return string The extension of the file.
     */
    public static function getExt($filename)
    {
        return substr(strrchr($filename, '.'), 1);
    }

    /**
     * Detects the end-of-line character(s) of a string.
     *
     * @param string $string String to check.
     *
     * @return string The detected EOL.
     */
    public static function detectEol($string)
    {
        $eols = array_count_values(str_split(preg_replace("/[^\r\n]/", '', $string)));
        $eola = array_keys($eols, max($eols));
        $eol = implode('', $eola);

        return $eol;
    }
}
