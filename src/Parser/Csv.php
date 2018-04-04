<?php

/**
 * CSV Parser class.
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
 * Parses CSV strings to array.
 * 
 * @author Adam Blake <theadamattack@gmail.com>
 * @copyright (c) 2016, Adam Blake <theadamattack@gmail.com>
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License (GPL-3.0)
 */
class Csv implements ParserInterface
{
    /**
     * {@inheritdoc}
     * 
     * Parses a CSV file into a two-dimensional array where each line of the 
     * CSV data is an indexed array within the output array.
     * 
     * @param string $csv    The CSV data to parse.
     * @param bool   $header Set FALSE if the data does not have a header row.
     * @param string $delim  The delimiter used to separate data fields.
     * @param string $enc    The character used to enclose fields.
     * 
     * @return array The two-dimensional array of data.
     * @throws ParseException if the file or string cannot be parsed.
     */
    public static function parse(
        string $csv,
        bool $header = true,
        string $delim = ',',
        string $enc = '"'
    ): array {
        $lines = explode("\n", rtrim(self::encodeEnclosures($csv, $delim, $enc), "\n"));
        $parsed = $header ? self::parseLinesWithHeader($lines, $delim, $enc)
                          : self::parseLinesWithNoHeader($lines, $delim, $enc);
        
        return $parsed;
    }
    
    /**
     * Parses a CSV file into a two-dimensional array where each line of the 
     * CSV data is an indexed array within the output array.
     * 
     * @param array  $lines  The array of CSV data rows to parse.
     * @param string $delim  The delimiter used to separate data fields.
     * @param string $enc    The character used to enclose fields.
     * 
     * @return array The two-dimensional array of data.
     */
    private static function parseLinesWithNoHeader(
        array $lines,
        string $delim,
        string $enc
    ): array {
        foreach ($lines as &$line) {
            $line = self::parseEncodedLine($line, $delim, $enc);
        }
            
        return $lines;
    }
    
    /**
     * Parses an array of encoded CSV lines into a two-dimensional array using 
     * the values in the first line as the keys for subsequent lines.
     * 
     * @param array $lines   The full array of CSV data to parse.
     * @param string $delim  The delimiter used to separate data fields.
     * @param string $enc    The character used to enclose fields.
     * 
     * @return array The two-dimensional array of data.
     * @throws ParseException if the strings cannot be parsed.
     */
    private static function parseLinesWithHeader(
        array $lines,
        string $delim,
        string $enc
    ): array {
        $header = self::parseEncodedLine(array_shift($lines), $delim, $enc);
        $headLen = count($header);
        foreach ($lines as $idx => &$line) {
            $data = self::parseEncodedLine($line, $delim, $enc);
            self::checkForLongRow($data, $idx + 2, $headLen);
            $line = array_combine($header, array_pad($data, $headLen, ''));
        }
        
        return $lines;
    }
    
    /**
     * Stops the parse methods if a data row has more fields than the header.
     * 
     * @param array $row  The row of data to check.
     * @param int $lineNo The line number in the CSV file that the row occurs.
     * @param int $maxLen The maximum length allowed (i.e. length of header).
     * 
     * @throws ParseException if a data row has more fields than the header.
     */
    private static function checkForLongRow(array $row, int $lineNo, int $maxLen)
    {
        if (count($row) > $maxLen) {
            throw new ParseException("The row of data on line {$lineNo} has "
                . "more fields than the header. Data: " . json_encode($row));
        }
    }
    
    /**
     * Replaces all special characters found in enclosures with markers.
     * 
     * Certain characters are important for parsing CSVs: the delimiter, 
     * the enclosure, line feed, and carriage return. The delimiter, line feed,
     * and carriage return are allowed within enclosures, and the enclosure
     * character must be escaped by itself within enclosures. This method
     * replaces these characters with markers if they are found within
     * enclosures.
     * 
     * @param string $csv   The CSV data to encode.
     * @param string $delim The delimiter used to separate data fields.
     * @param string $enc   The character used to enclose fields.
     * 
     * @return string The CSV data with enclosures removed and markers inserted.
     * @throws ParseException if there is a PCRE PREG error.
     * 
     * @see decodeMarkers, parseEncodedCsvLine
     */
    private static function encodeEnclosures(
        string $csv,
        string $delim,
        string $enc
    ): string {
        // Pattern for matching non-enclosed data: ([^"]*)
        // Pattern for matching enclosed data: (?:"((?:""|[^"])*)")*
        $pattern = '/([^"]*)(?:"((?:""|[^"])*)")*/s';
        if ($enc !== '"') { $pattern = str_replace('"', $enc, $pattern); }

        $encoded = preg_replace_callback($pattern, function ($m) use ($delim, $enc) {
            $enclosed = isset($m[2]) ? self::encodeMarkers($m[2], $delim, $enc) : '';
            return self::convertToUnixLineEndings($m[1]) . $enclosed;
        }, $csv);

        if (null === $encoded) {
            $errorMessage = array_flip(get_defined_constants(true)['pcre'])[preg_last_error()];
            throw new ParseException($errorMessage);
        }

        return $encoded;
    }
    
    /**
     * Encodes special characters as special marker sequences so that they are
     * not parsed as CSV control characters.
     * 
     * @param string $enclosure The enclosure with characters to encode.
     * @param string $delim     The delimiter used to separate data fields.
     * @param string $enc       The character used to enclose fields.
     * 
     * @return string Returns the string with CSV special characters encoded.
     */
    private static function encodeMarkers(string $enclosure, string $delim, string $enc): string {
        $characters = [$delim, $enc.$enc, "\n", "\r"];
        $markers = ['!!D!!', '!!E!!', '!!N!!', '!!R!!'];
        
        return str_replace($characters, $markers, $enclosure);
    }
    
    /**
     * Converts all line endings to Unix-style line endings (LF).
     * 
     * @param string $string The string with characters to convert.
     * 
     * @return string Returns the string with only Unix-style line endings.
     */
    private static function convertToUnixLineEndings(string $string): string {
        return str_replace(["\r\n", "\r"], ["\n", "\n"], $string);
    }
    
    /**
     * Parses and decodes a CSV line encoded with encodeEnclosures().
     * 
     * @param string $line  The line of CSV data to parse.
     * @param string $delim The delimiter used to separate data fields.
     * @param string $enc   The character used to enclose fields.
     * 
     * @return array The indexed array of the CSV data fields.
     * 
     * @see encodeEnclosures, decodeMarkers
     */
    private static function parseEncodedLine(
        string $line,
        string $delim,
        string $enc
    ): array {
        $fields = explode($delim, $line);
        foreach ($fields as &$field) {
            $field = self::decodeMarkers($field, $delim, $enc);
        }
        
        return $fields;
    }

    /**
     * Restores markers to the appropriate special characters.
     * 
     * @param string $field The field to decode.
     * @param string $delim The delimiter used to separate data fields.
     * @param string $enc   The character used to enclose fields.
     * 
     * @return string The data field with special characters restored.
     * 
     * @see encodeEnclosures
     */
    private static function decodeMarkers(
        string $field,
        string $delim,
        string $enc
    ): string {
        $markers = ['!!D!!', '!!E!!', '!!N!!', '!!R!!'];
        $characters = [$delim, $enc, "\n", "\r"];
        
        return str_replace($markers, $characters, $field);
    }
}
