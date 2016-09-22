<?php

/**
 * Parse class.
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

namespace adamblake\parse;

use adamblake\parse\Parser\ParserInterface;

/**
 * Collection of static functions for parsing files.
 *
 * @author Adam Blake <theadamattack@gmail.com>
 * @copyright (C) 2016 Adam Blake <theadamattack@gmail.com>
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
class Parse
{
    /**
     * Parses a configuration file and returns an associative array.
     *
     * @param string $filename     The configuration file to parse.
     *
     * @return array|\stdClass Associative array or class of the configurations.
     *
     * @throws ParseException if the file type is unsupported.
     */
    public static function config($filename): array
    {
        $ext = strtolower(self::getExt($filename));

        if ('yml' === $ext || 'yaml' === $ext) {
            $config = self::yaml($filename, false);
        } elseif ('json' === $ext) {
            $config = self::json($filename, false);
        } elseif ('ini' === $ext) {
            $config = self::ini($filename, false);
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
     * The first row will be interpreted as a header row and the values used
     * as keys (column names) for the subsequent rows).
     *
     * @param string $input        The file or string of CSV data to parse.
     * @param bool   $isString     Set TRUE to indicate that the data is a
     *                             string, not a file holding the data.
     * @param bool   $header       Set FALSE to parse data without a header.
     * @param string $delimiter    The delimiter used to separate data fields.
     * @param string $enclosure    The character used to enclose fields.
     *
     * @return array The parsed data.
     */
    public static function csv(
        string $input,
        bool $isString = false,
        bool $header = true,
        string $delimiter = ',',
        string $enclosure = '"'
    ): array {
        $parser = self::getParser(__FUNCTION__);
        $params = [$header, $delimiter, $enclosure];

        return self::parse($parser, $input, $isString, $params);
    }

    /**
     * Parses a YAML file or string.
     *
     * @param string $input        The file or string of data to parse.
     * @param bool   $isString     Set true to indicate that the data is a
     *                             string, not a file holding the data.
     * @param bool   $returnObject Set true to return a \stdClass object.
     *
     * @return array The parsed data.
     */
    public static function yaml(
        string $input,
        bool $isString = false,
        bool $returnObject = false
    ): array {
        $parser = self::getParser(__FUNCTION__);

        return self::parse($parser, $input, $isString);
    }

    /**
     * Parses a JSON file or string.
     *
     * @param string $input        The file or string of data to parse.
     * @param bool   $isString     Set true to indicate that the data is a
     *                             string, not a file holding the data.
     *
     * @return array The parsed data.
     */
    public static function json(
        string $input,
        bool $isString = false
    ): array {
        $parser = self::getParser(__FUNCTION__);

        return self::parse($parser, $input, $isString);
    }

    /**
     * Parses an INI file or string.
     *
     * @param string $input        The file or string of data to parse.
     * @param bool   $isString     Set true to indicate that the data is a
     *                             string, not a file holding the data.
     *
     * @return array The parsed data.
     */
    public static function ini(
        string $input,
        bool $isString = false
    ): array {
        $parser = self::getParser(__FUNCTION__);

        return self::parse($parser, $input, $isString);
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
     * 
     * @codeCoverageIgnore
     */
    public static function fileGetContents(): string
    {
        set_error_handler(ParseException::class . '::errorHandler');
        $contents = call_user_func_array('file_get_contents', func_get_args());
        restore_error_handler();

        return $contents;
    }

    /**
     * Determines the extension of a given file.
     *
     * @param string $filename The path to the file.
     *
     * @return string The extension of the file.
     */
    public static function getExt(string $filename): string
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
    public static function detectEol(string $string): string
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
     * @param ParserInterface $parser   The Parser to use.
     * @param string          $input    The file or string of data to parse.
     * @param bool            $isString Set true to indicate that the data is a
     *                                  string, not a file holding the data.
     * @param array           $params   Additional parameters to pass to the
     *                                  parser.
     *
     * @return array The parsed data.
     */
    private static function parse(
        ParserInterface $parser,
        string $input,
        bool $isString = false, 
        array $params = []
    ): array {
        $contents = $isString ? $input : self::fileGetContents($input);
        $allParams = array_merge([$contents], $params);
        
        return $parser::parse(...$allParams);
    }
    
    /**
     * Returns a new instance of the desired parser.
     *
     * @param string $name The short name of the desired class.
     * 
     * @return ParserInterface Returns the desired Parser.
     * 
     * @throws ParseException if the parser cannot be loaded.
     */
    private static function getParser($name)
    {
        $class = __NAMESPACE__ . '\\Parser\\' . ucfirst(strtolower($name));
        
        return new $class;
    }
}
