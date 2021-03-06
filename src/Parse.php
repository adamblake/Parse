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
    public static function parse(string $filename): array
    {
        $extension = self::getExt($filename);
        if (in_array($extension, ['yml', 'yaml', 'json', 'ini'])) {
            return self::config($filename);
        }

        if (in_array($extension, ['csv', 'txt', 'tsv', 'xlsx'])) {
            return self::table($filename);
        }

        throw new ParseException(sprintf(
            'The given file "%s" is of an unsupported file type (supported: YAML, JSON, INI, CSV, TSV, XLSX).', $filename)
        );
    }

    /**
     * Parses a configuration file and returns an associative array.
     *
     * @param string $filename     The configuration file to parse.
     *
     * @return array Associative array of the configurations.
     * @throws ParseException if the file type is unsupported.
     */
    public static function config(string $filename): array
    {
        switch (strtolower(self::getExt($filename))) {
            case 'yml':
            case 'yaml': return self::yaml($filename, false);
            case 'json': return self::json($filename, false);
            case 'ini':  return self::ini($filename, false);
            default:     throw new ParseException('The given config file '
                         . "'$filename' is invalid or of an unsupported file "
                         . 'type (supported: YAML, JSON, INI.');
        }
    }

    /**
     * Parses a table and returns the array of data.
     *
     * Note 'TXT' files are treated as tab-delimited as this is the default
     * Excel extension for tab-delimited data.
     *
     * @param string $filename The table file to parse.
     * @param bool   $header   Set FALSE to parse without header row.
     *
     * @return array The array of data from the table.
     * @throws ParseException if the file type is unsupported.
     */
    public static function table(string $filename, bool $header = true): array
    {
        switch (strtolower(self::getExt($filename))) {
            case 'csv':  return self::csv($filename, false, $header);
            case 'txt':  // default Excel extension for tsv
            case 'tsv':  return self::tsv($filename, false, $header);
            case 'xlsx': return self::xlsx($filename, $header);
            default:     throw new ParseException('The given config file '
                         . "'$filename' is invalid or of an unsupported file "
                         . 'type (supported: CSV, TSV, XLSX.');
        }
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
     * @throws ParseException if the file or string cannot be parsed.
     */
    public static function yaml(string $input, bool $isString = false, bool $returnObject = false): array
    {
        $parser = self::getParser(__FUNCTION__);

        return self::parseWith($parser, $input, $isString);
    }

    /**
     * Parses a JSON file or string.
     *
     * @param string $input        The file or string of data to parse.
     * @param bool   $isString     Set true to indicate that the data is a
     *                             string, not a file holding the data.
     *
     * @return array The parsed data.
     * @throws ParseException if the file or string cannot be parsed.
     */
    public static function json(string $input, bool $isString = false): array
    {
        $parser = self::getParser(__FUNCTION__);

        return self::parseWith($parser, $input, $isString);
    }

    /**
     * Parses an INI file or string.
     *
     * @param string $input        The file or string of data to parse.
     * @param bool   $isString     Set true to indicate that the data is a
     *                             string, not a file holding the data.
     *
     * @return array The parsed data.
     * @throws ParseException if the file or string cannot be parsed.
     */
    public static function ini(string $input, bool $isString = false): array {
        $parser = self::getParser(__FUNCTION__);

        return self::parseWith($parser, $input, $isString);
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
     * @throws ParseException if the file or string cannot be parsed.
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

        return self::parseWith($parser, $input, $isString, $params);
    }

    /**
     * Shortcut wrapper function for tab-separated data. Operates as csv with
     * delimiter set to tab.
     *
     * @param string $input        The file or string of TSV data to parse.
     * @param bool   $isString     Set TRUE to indicate that the data is a
     *                             string, not a file holding the data.
     * @param bool   $header       Set FALSE to parse data without a header.
     * @param string $enclosure    The character used to enclose fields.
     *
     * @return array The parsed data.
     * @throws ParseException if the file or string cannot be parsed.
     */
    public static function tsv(
        string $input,
        bool $isString = false,
        bool $header = true,
        string $enclosure = '"'
    ): array {
        $parser = self::getParser('csv');
        $params = [$header, "\t", $enclosure];

        return self::parseWith($parser, $input, $isString, $params);
    }

    /**
     * Parses an XLSX file.
     * Unlike other parsers, this does not support a "string" input of the data,
     * as it is impractical to try to parse that type of input (XLSX is a
     * complicated format). Thus, there is no $isString parameter.
     *
     * @param string $filename The file to parse.
     * @param bool   $header   Set FALSE to parse data without a header.
     *
     * @return array The parsed data.
     * @throws ParseException if the file cannot be parsed.
     */
    public static function xlsx(string $filename, bool $header = true): array {
        $parser = self::getParser(__FUNCTION__);

        return $parser::parse($filename, $header);
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
     * @throws ParseException if the file or string cannot be parsed.
     */
    private static function parseWith(
        ParserInterface $parser,
        string $input,
        bool $isString = false,
        array $params = []
    ): array {
        $contents = $isString ? $input : file_get_contents($input);
        if (false === $contents) {
            throw new ParseException(sprintf('The file "%s" could not be read.', $input));
        }

        $allParams = array_merge([$contents], $params);

        return $parser::parse(...$allParams);
    }

    /**
     * Returns a new instance of the desired parser.
     *
     * If the class cannot be loaded, a ParseException may be thrown.
     *
     * @param string $name The short name of the desired class.
     *
     * @return ParserInterface Returns the desired Parser.
     */
    private static function getParser(string $name): ParserInterface
    {
        $class = __NAMESPACE__ . '\\Parser\\' . ucfirst(strtolower($name));

        return new $class;
    }
}
