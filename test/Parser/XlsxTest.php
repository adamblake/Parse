<?php

/**
 * PHPUnit test class for the Xlsx class.
 */

namespace adamblake\parse\Parser;

use adamblake\parse\ParseException;

/**
 * Tests the behavior of the Xlsx class.
 * 
 * @author Adam Blake <theadamattack@gmail.com>
 * @copyright (c) 2016, Adam Blake <theadamattack@gmail.com>
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License (GPL-3.0)
 */
class XlsxTest extends ParserTestFramework
{
    /**
     * {@inheritdoc}
     * 
     * @return string The type of Parser.
     */
    protected function getType()
    : string {
        return 'xlsx';
    }
    
    public function testParseEmptyFileReturnsEmptyArray()
    {
        $this->assertEquals([], Xlsx::parse($this->files.'/empty.xlsx'));
    }
    
    public function testParseInvalidFileThrowsParseException()
    {
        $this->expectException(ParseException::class);
        Xlsx::parse($this->files.'/csv.xlsx');
    }
    
    public function testParseSimpleXlsxDataWithOneRow()
    {
        $actual = Xlsx::parse($this->files.'/simple.xlsx', false);
        $this->assertEquals([['one', 'two', 'three']], $actual);
    }
    
    public function testParseXlsxSheetWithMultipleRows()
    {
        $actual = Xlsx::parse($this->files.'/multipleRows.xlsx', false);
        $this->assertEquals([[1, 2], [3, 4], [5, 6]], $actual);
    }
    
    public function testParseXlsxSheetWithHeader()
    {
        $actual = Xlsx::parse($this->files.'/multipleRows.xlsx');
        $this->assertEquals([[1 => 3, 2 => 4], [1 => 5, 2 => 6]], $actual);
    }
    
    public function testRowsWithEmptyCellsShouldStillHaveHeaderKeys()
    {
        $actual = Xlsx::parse($this->files.'/missingCells.xlsx');
        $this->assertEquals([
            ['a' => 'd','b' => 'e','c' => 'f'],
            ['a' => 'g','b' => '','c' => ''],
        ], $actual);
    }
    
    public function testRowsLongerThanHeaderShouldThrowExceptionWithRowNumber()
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessageRegExp('/line 3/');
        Xlsx::parse($this->files.'/rowTooLong.xlsx');
    }
}
