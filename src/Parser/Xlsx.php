<?php

/**
 * XLSX Parser class.
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
 * Parses XLSX sheets to array.
 * 
 * @author Adam Blake <theadamattack@gmail.com>
 * @copyright (c) 2016, Adam Blake <theadamattack@gmail.com>
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License (GPL-3.0)
 */
class Xlsx implements ParserInterface
{
    /**
     * {@inheritdoc}
     * 
     * Parses an XLSX file into a two-dimensional array where each row of the
     * XLSX data is an indexed array within the output array.
     * 
     * @param string $filename The file to parse.
     * @param bool   $header   Set FALSE to parse data without a header.
     * 
     * @return array The two-dimensional array of data.
     */
    public static function parse(string $filename, $header = true)
    : array {
        $sheet = self::readFile($filename)->getActiveSheet();
        $data = self::readSheet($sheet);
        if ($header && !empty($data)) {
            $data = self::processHeaderKeys($data);
        }
        
        return $data;
    }
    
    /**
     * Reads the file into a PHPExcel object.
     * 
     * @param string $filename The file to parse.
     * 
     * @return \PHPExcel Returns the PHPExcel object for the file.
     * 
     * @throws ParseException when the reader encounters an error.
     */
    private static function readFile(string $filename)
    : \PHPExcel {
        try {
            $reader = new \PHPExcel_Reader_Excel2007();
            $reader->setReadDataOnly(true);
            $xlsx = $reader->load($filename);
        } catch (\Exception $e) {
            throw new ParseException($e->getMessage());
        }
        
        return $xlsx;
    }
    
    /**
     * Reads a PHPExcel_Worksheet in as an array of row arrays.
     * 
     * @param \PHPExcel_Worksheet $sheet The sheet to read.
     * 
     * @return array Returns the array of data from the sheet.
     */
    private static function readSheet(\PHPExcel_Worksheet $sheet)
    : array {
        if (self::sheetIsEmpty($sheet)) {
            return [];
        }
        
        $data = [];
        foreach ($sheet->getRowIterator() as $rowNum => $row) {
            foreach ($row->getCellIterator() as $cell) {
                $data[$rowNum - 1][] = $cell->getCalculatedValue();
            }
        }
        
        return $data;
    }
    
    /**
     * Checks to see if the sheet is empty.
     * 
     * @param \PHPExcel_Worksheet $sheet The PHPExcel sheet to check.
     * 
     * @return bool Returns TRUE if the sheet is empty, else FALSE.
     */
    private static function sheetIsEmpty(\PHPExcel_Worksheet $sheet)
    : bool {
        return $sheet->getHighestDataRow() === 1
            && $sheet->getHighestDataColumn() === 'A'
            && $sheet->getCell('A1')->getValue() === null;
    }
    
    /**
     * Converts a two-dimensional array of indexed arrays to an array of
     * associative arrays, where the first row is used for the keys of the
     * subsequent rows.
     * 
     * @param array $data The data to process.
     * 
     * @return array The data with the first row used as keys for the others.
     */
    private static function processHeaderKeys(array $data)
    : array {
        $header = array_filter(array_shift($data));
        $headLen = count($header);
        foreach ($data as $idx => &$row) {
            self::checkForLongRow($row, $idx + 2, $headLen);
            $row = array_combine($header, array_pad($row, $headLen, ''));
        }
        
        return $data;
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
    private static function checkForLongRow(array &$row, int $lineNo, int $maxLen)
    {
        $rowLen = count($row);
        $extraCells = $rowLen - $maxLen;
        while ($extraCells > 0) {
            if (isset($row[$maxLen + $extraCells - 1])) {
                throw new ParseException("The row of data on line {$lineNo} has"
                . " more fields than the header. Data: " . json_encode($row));
            }
            unset($row[$maxLen + $extraCells - 1]);
            --$extraCells;
        }
    }
}
