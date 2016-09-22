<?php

namespace adamblake\parse\Parser;

use adamblake\parse\ParseException;

class IniTest extends ParserTestFramework
{
    /**
     * {@inheritdoc}
     * 
     * @return string The type of Parser.
     */
    protected function getType()
    : string {
        return 'ini';
    }
    
    /**
     * @covers adamblake\parse\Parser\Ini::parse
     */
    public function testParseEmptyStringReturnsEmptyArray()
    {
        $actual = Ini::parse('');
        $this->assertInternalType('array', $actual);
        $this->assertEmpty($actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Ini::parseIniString
     */
    public function testParseIniStringReturnsSameInputAsWrappedFunction()
    {
        $test = 'key=value';
        $this->assertEquals(parse_ini_string($test), Ini::parseIniString($test));
    }
    
    /**
     * @covers adamblake\parse\Parser\Ini::parseIniString
     */
    public function testParseIniStringInvalidThrowsExceptionNotError()
    {
        $this->expectException(ParseException::class);
        Ini::parseIniString('[');
    }
    
    /**
     * @covers adamblake\parse\Parser\Ini::parse
     */
    public function testParseIniStringWithMultipleKeys()
    {
        $actual = Ini::parse("id=0\nname=Adam\nphrase=\"Here's me\"");
        $expected = ['id' => 0, 'name' => 'Adam', 'phrase' => "Here's me"];
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Ini::unpackNestedKeys
     * @covers adamblake\parse\Parser\Ini::nest
     */
    public function testParseSinglyNestedIniData()
    {
        $actual = Ini::parse("0.name=Adam");
        $expected = [0 => ['name' => 'Adam']];
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * @covers adamblake\parse\Parser\Ini::unpackNestedKeys
     * @covers adamblake\parse\Parser\Ini::nest
     */
    public function testParseDoublyNestedIniData()
    {
        $actual = Ini::parse("0.name.first=Adam");
        $expected = [0 => ['name' => ['first' => 'Adam']]];
        $this->assertEquals($expected, $actual);
    }
}
