<?php

/**
 * Parse class.
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

namespace adamblake\Parse;

/**
 * Collection of static functions for parsing files.
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 * @copyright (C) 2016 Adam Blake <adamblake@g.ucla.edu>
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
class Parse
{
    /**
     * Parses a configuration file and returns an associative array.
     *
     * @param string $filename     The configuration file to parse.
     * @param bool   $returnObject Set true to return a \stdClass object.
     *
     * @return array|\stdClass Associative array or class of the configurations.
     *
     * @throws ParseException An exception is thrown for unsupported files.
     */
    public static function config($filename, $returnObject = false)
    {
        $ext = strtolower(self::getExt($filename));

        if ('yml' === $ext || 'yaml' === $ext) {
            $config = self::yaml($filename, false, $returnObject);
        } elseif ('json' === $ext) {
            $config = self::json($filename, false, $returnObject);
        } elseif ('ini' === $ext) {
            $config = self::ini($filename, false, $returnObject);
        }

        if (!isset($config)) {
            throw new ParseException('The given config file '
                ."'$filename' is invalid or of an unsupported file type "
                .'(supported: YAML, JSON, INI.');
        }

        return $config;
    }

    /**
     * Parses a CSV file or string.
     * The first row will be interpretted as a header row and the values used
     * as keys (column names) for the subsequent rows).
     *
     * @param string $input        The file or string of data to parse.
     * @param bool   $isString     Set true to indicate that the data is a
     *                             string, not a file holding the data.
     * @param bool   $returnObject Set true to return a \stdClass object.
     * @param string $delimiter    The delimiter to use when parsing the data.
     * @param bool   $header       Set false to parse data without a header.
     *
     * @return array|\stdClass The parsed data.
     */
    public static function csv($input, $isString = false, $returnObject = false,
        $delimiter = ',', $header = true
    ) {
        $parser = self::getParseMethod(__FUNCTION__);
        $params = array($delimiter, $header);

        return self::parse($parser, $input, $isString, $returnObject, $params);
    }

    /**
     * Parses a YAML file or string.
     *
     * @param string $input        The file or string of data to parse.
     * @param bool   $isString     Set true to indicate that the data is a
     *                             string, not a file holding the data.
     * @param bool   $returnObject Set true to return a \stdClass object.
     *
     * @return array|\stdClass The parsed data.
     */
    public static function yaml($input, $isString = false, $returnObject = false)
    {
        $parser = self::getParseMethod(__FUNCTION__);

        return self::parse($parser, $input, $isString, $returnObject);
    }

    /**
     * Parses a JSON file or string.
     *
     * @param string $input        The file or string of data to parse.
     * @param bool   $isString     Set true to indicate that the data is a
     *                             string, not a file holding the data.
     * @param bool   $returnObject Set true to return a \stdClass object.
     *
     * @return array|\stdClass The parsed data.
     */
    public static function json($input, $isString = false, $returnObject = false)
    {
        $parser = self::getParseMethod(__FUNCTION__);

        return self::parse($parser, $input, $isString, $returnObject);
    }

    /**
     * Parses an INI file or string.
     *
     * @param string $input        The file or string of data to parse.
     * @param bool   $isString     Set true to indicate that the data is a
     *                             string, not a file holding the data.
     * @param bool   $returnObject Set true to return a \stdClass object.
     *
     * @return array|\stdClass The parsed data.
     */
    public static function ini($input, $isString = false, $returnObject = false)
    {
        $parser = self::getParseMethod(__FUNCTION__);

        return self::parse($parser, $input, $isString, $returnObject);
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
     * @throws ParseException Throws an exception when file_get_contents cannot
     *                        open the file.
     *
     * @see \file_get_contents()
     */
    public static function fileGetContents()
    {
        set_error_handler(__NAMESPACE__.'\Parse::errorException');
        $contents = call_user_func_array('file_get_contents', func_get_args());
        restore_error_handler();

        return $contents;
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
     * @throws ParseException Errors are redirected through this exception.
     */
    public static function errorException($num, $str, $file, $line)
    {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }

        throw new ParseException($str, 0, $num, $file, $line);
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

    /**
     * Calls the parser method with the contents of input and returns the data
     * in array (or optionally object) form.
     *
     * @param string $parser       The fully qualified method to use.
     * @param string $input        The file or string of data to parse.
     * @param bool   $isString     Set true to indicate that the data is a
     *                             string, not a file holding the data.
     * @param bool   $returnObject Set true to return a \stdClass object.
     * @param array  $params       Additional parameters to pass to the parser.
     *
     * @return array|\stdClass The parsed data.
     */
    protected static function parse($parser, $input, $isString = false,
        $returnObject = false, $params = array()
    ) {
        $contents = $isString ? $input : self::fileGetContents($input);
        $allParams = array_merge(array($contents), $params);
        $parsed = call_user_func_array($parser, $allParams);

        return $returnObject ? self::arrayToObject($parsed) : $parsed;
    }

    /**
     * Returns the fully qualified name of the given parser.
     *
     * @param string $name The short name of the desired class.
     *
     * @return string|bool The class name or false if the class could not be
     *                     found or does not implement IParser.
     */
    protected static function getParseMethod($name)
    {
        $ns = __NAMESPACE__.'\\Parser\\';
        $cls = $ns.ucfirst(strtolower($name));
        $mtd = $cls.'::parse';

        $int = $ns.'IParser';
        $imp = class_implements($cls);

        return (class_exists($cls) && $imp && isset($imp[$int])) ? $mtd : false;
    }
}