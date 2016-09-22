<?php

namespace adamblake\parse\Parser;

use adamblake\parse\ParseException;

class JsonTest extends ParserTestFramework
{
    /**
     * {@inheritdoc}
     * 
     * @return string The type of Parser.
     */
    protected function getType()
    : string {
        return 'json';
    }
    
    /**
     * @covers adamblake\parse\Parser\Json::parse
     */
    public function testParseEmptyReturnsEmptyArray()
    {
        $actual = Json::parse('');
        $this->assertInternalType('array', $actual);
        $this->assertEmpty($actual);
    }

    /**
     * @covers adamblake\parse\Parser\Json::parse
     */
    public function testParseInvalid()
    {
        $this->expectException(ParseException::class);
        Json::parse('[');
    }
    
    /**
     * @covers adamblake\parse\Parser\Json::parse
     */
    public function testParseValid()
    {
        $actual = Json::parse('{"gloss":{"title": "S","type":["GML", "XML"]}}');
        $expected = ['gloss' => ['title' => 'S', 'type' => ["GML", "XML"]]];
        $this->assertEquals($expected, $actual);
    }
}
