<?php

namespace adamblake\parse\Parser;

use adamblake\parse\ParseException;

class CsvTest extends ParserTestFramework
{
    /**
     * {@inheritdoc}
     *
     * @return string The type of Parser.
     */
    protected function getType(): string
    {
        return 'csv';
    }
    
    /**
     * The array of data in the 'valid' test file.
     * @var array
     */
    protected $data = [
        ['id' => '0000', 'name' => 'Adam', 'sentence' => 'has, a comma'],
        ['id' => '0001', 'name' => 'Brad', 'sentence' => '"is quoted"'],
    ];

    /**
     * @covers adamblake\parse\Parser\Csv::parse
     * @covers adamblake\parse\Parser\Csv::parseLinesWithNoHeader
     * @covers adamblake\parse\Parser\Csv::parseEncodedLine
     */
    public function testParsesSimpleCsvData()
    {
        $actual = Csv::parse('this,has,commas', false);
        $this->assertEquals([['this', 'has', 'commas']], $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Csv::parse
     */
    public function testCanParseCsvDataWithCustomDelimiters()
    {
        $actual = Csv::parse('this;has;semicolons', false, ';');
        $this->assertEquals([['this', 'has', 'semicolons']], $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Csv::parse
     */
    public function testParsesCsvDataWithSpaces()
    {
        $actual = Csv::parse("this has, spaces", false);
        $this->assertEquals([['this has', ' spaces']], $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Csv::encodeEnclosures
     * @covers adamblake\parse\Parser\Csv::parseEncodedLine
     * @covers adamblake\parse\Parser\Csv::decodeMarkers
     * @covers adamblake\parse\Parser\Csv::encodeMarkers
     */
    public function testParsesCsvDataWithEnclosures()
    {
        $actual = Csv::parse('"enclosed","data","here"', false);
        $this->assertEquals([['enclosed', 'data', 'here']], $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Csv::encodeEnclosures
     * @covers adamblake\parse\Parser\Csv::parseEncodedLine
     * @covers adamblake\parse\Parser\Csv::decodeMarkers
     * @covers adamblake\parse\Parser\Csv::encodeMarkers
     */
    public function testParsesCsvDataWithDelimiterInEnclosure()
    {
        $actual = Csv::parse('some,"text,with",comma', false);
        $this->assertEquals([['some', 'text,with', 'comma']], $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Csv::encodeEnclosures
     * @covers adamblake\parse\Parser\Csv::parseEncodedLine
     * @covers adamblake\parse\Parser\Csv::decodeMarkers
     * @covers adamblake\parse\Parser\Csv::encodeMarkers
     */
    public function testCanParseCsvWithCustomEnclosures()
    {
        $actual = Csv::parse('#enclosed#,#data#,#here#', false, ',', '#');
        $this->assertEquals([['enclosed', 'data', 'here']], $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Csv::encodeEnclosures
     * @covers adamblake\parse\Parser\Csv::parseEncodedLine
     * @covers adamblake\parse\Parser\Csv::decodeMarkers
     * @covers adamblake\parse\Parser\Csv::encodeMarkers
     */
    public function testCanParseCsvWithSpecialCharacters()
    {
        $actual = Csv::parse("\"special\r\n,chars\",\"here\"\"\"", false);
        $this->assertEquals([["special\r\n,chars", 'here"']], $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Csv::parse
     */
    public function testCanParseCsvWithMultipleRowsSplitByLF()
    {
        $actual = Csv::parse("a,b,c\nd,e,f", false);
        $this->assertEquals([['a','b','c'], ['d','e','f']], $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Csv::convertToUnixLineEndings
     */
    public function testCanParseCsvWithMultipleRowsSplitByCRLF()
    {
        $actual = Csv::parse("a,b,c\r\nd,e,f", false);
        $this->assertEquals([['a','b','c'], ['d','e','f']], $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Csv::convertToUnixLineEndings
     */
    public function testCanParseCsvWithMultipleRowsSplitByCR()
    {
        $actual = Csv::parse("a,b,c\rd,e,f", false);
        $this->assertEquals([['a','b','c'], ['d','e','f']], $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Csv::parseLinesWithHeader
     */
    public function testCanParseCsvWithHeader()
    {
        $actual = Csv::parse("a,b,c\nd,e,f\ng,h,i");
        $this->assertEquals([
            ['a' => 'd','b' => 'e','c' => 'f'],
            ['a' => 'g','b' => 'h','c' => 'i'],
        ], $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Csv::parse
     */
    public function testParseEmptyCsvReturnsEmptyArray()
    {
        $actual = Csv::parse('');
        $this->assertInternalType('array', $actual);
        $this->assertEmpty($actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Csv::parseLinesWithHeader
     */
    public function testRowsWithEmptyCellsShouldStillHaveHeaderKeys()
    {
        $actual = Csv::parse("a,b,c\nd,e,f\ng");
        $this->assertEquals([
            ['a' => 'd','b' => 'e','c' => 'f'],
            ['a' => 'g','b' => '','c' => ''],
        ], $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Csv::checkForLongRow
     */
    public function testRowsLongerThanHeaderShouldThrowExceptionWithRowNumber()
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessageRegExp('/line 3/');
        Csv::parse("a,b,c\nd,e,f\ng,h,i,j");
    }
    
    public function testCanHandleExcelGeneratedCsvFiles()
    {
        $contents = file_get_contents($this->files.'/multipleRows.excel.csv');
        $actual = Csv::parse($contents, false);
        $this->assertEquals([[1, 2], [3, 4], [5, 6]], $actual);
    }
}
