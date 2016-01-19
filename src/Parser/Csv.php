<?php

/**
 * Csv parser class.
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
 * Parses CSV strings to array.
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 * @copyright (C) 2016 Adam Blake <adamblake@g.ucla.edu>
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
class Csv implements IParser
{
    /**
     * {@inheritdoc}
     *
     * @param string $string    The string of data to parse.
     * @param string $delimiter The delimiter to use when parsing the data.
     * @param bool   $header    Set false to parse data without a header.
     *
     * @return array The parsed data.
     *
     * @throws adamblake\Parse\ParseException Throws an exception if the string is invalid.
     */
    public static function parse($string, $delimiter = ',', $header = true)
    {
        // first find enclosed fields and make placeholders for special chars
        $pos = 0;
        while ($enc = self::getNextEnclosure($string, $pos)) {
            // get start of enclosure
            $encStart = strpos($string, $enc, $pos) - 1;

            // get end of enclosure
            $encEnd = $encStart + strlen($enc) + 2;

            // encode the enclosure
            $encodedEnc = self::encodeSpecialChars($enc);
            $string = substr($string, 0, $encStart) . $encodedEnc . substr($string, $encEnd);

            // update position
            $pos = $encStart + strlen($encodedEnc);
        }

        // convert end of line characters to Unix style
        $csv = preg_replace(array('/\r?\n/', '/\n$/'), array("\n", ''), $string);

        // split by lines and fields and decode special characters
        $lines = explode("\n", $csv);

        return $header ? self::parseLinesWithHeader($lines, $delimiter)
                       : self::parseLinesNoHeader($lines, $delimiter);
    }

    /**
     * Parses an array of encoded lines using the first line as a header.
     *
     * @param array  $lines     The array of encoded strings.
     * @param string $delimiter The delimiter to use when splitting the fields.
     *
     * @return array The array of decoded data.
     */
    protected static function parseLinesWithHeader(array $lines, $delimiter)
    {
        $header = self::parseLine(array_shift($lines), $delimiter);
        $headerlen = count($header);

        $data = array();
        foreach ($lines as $line) {
            $line = self::parseLine($line, $delimiter);
            self::normalizeRowLength($line, $headerlen);
            $data[] = array_combine($header, $line);
        }

        return $data;
    }

    /**
     * Parses an array of encoded lines without using the first line as a header.
     *
     * @param array  $lines     The array of encoded strings.
     * @param string $delimiter The delimiter to use when splitting the fields.
     *
     * @return array The array of decoded data.
     */
    protected static function parseLinesNoHeader(array $lines, $delimiter)
    {
        foreach ($lines as &$line) {
            $line = self::parseLine($line, $delimiter);
        }

        return $lines;
    }

    /**
     * Parses a line by splitting by the delimiter and then decoding each field.
     *
     * @param string $line      The line of CSV to parse.
     * @param string $delimiter The delimiter to split by.
     *
     * @return array The array of values from the line.
     */
    protected static function parseLine($line, $delimiter)
    {
        $values = explode($delimiter, $line);
        foreach ($values as &$field) {
            $field = self::decodeSpecialChars($field);
        }

        return $values;
    }

    /**
     * Returns the text inside the next CSV enclosure.
     * @param string $string The string to search.
     * @param int    $pos    The start position for the search.
     *
     * @return bool|string Returns the text within the next enclosure (not
     *                     including the enclosure) or false if there are none.
     *
     * @throws ParseException Throws an exception if an enclosure is opened in
     *                        the string but is not properly closed.
     */
    protected static function getNextEnclosure($string, $pos = 0)
    {
        $start = strpos($string, '"', $pos);
        if ($start === false) {
            return false;
        }

        $matches = array();
        $pattern = '/(?:[^"]*)"((?:""|[^"])*)"/';

        if (!preg_match($pattern, $string, $matches, 0, $start)) {
            throw new ParseException('The given data is not valid CSV.');
        }

        return $matches[1];
    }

    /**
     * Converts the given array to the specified length.
     *
     * @param array $array The array to modify.
     * @param int   $len   The length to match.
     */
    protected static function normalizeRowLength(array &$array, $len)
    {
        if (count($array) > $len) {
            $array = array_slice($array, 0, $len);
        }

        while (count($array) < $len) {
            $array[] = '';
        }
    }

    /**
     * Encodes CSV special characters to placeholder values.
     *
     * @param string $string The string to encode.
     *
     * @return string Returns the encoded string.
     *
     * @uses Csv::specialChars
     */
    protected static function encodeSpecialChars($string)
    {
        $specialChars = array(
            "\r" => '!!R!!',
            "\n" => '!!N!!',
            '""' => '!!Q!!',
            ','  => '!!C!!',
        );
        foreach ($specialChars as $char => $enc) {
            $string = str_replace($char, $enc, $string);
        }

        return $string;
    }

    /**
     * Decodes a string encoded using Csv::encodeSpecialChars.
     *
     * @param string $string The string to decode.
     *
     * @return string Returns the decoded string.
     *
     * @see Csv::encodeSpecialChars
     * @uses Csv::specialChars
     */
    protected static function decodeSpecialChars($string)
    {
        $specialChars = array(
            "\r" => '!!R!!',
            "\n" => '!!N!!',
            '"' => '!!Q!!',
            ','  => '!!C!!',
        );
        foreach ($specialChars as $char => $enc) {
            $string = str_replace($enc, $char, $string);
        }

        return $string;
    }
}
