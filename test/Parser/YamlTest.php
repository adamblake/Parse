<?php

namespace adamblake\parse\Parser;

use adamblake\parse\ParseException;

class YamlTest extends ParserTestFramework
{
    /**
     * {@inheritdoc}
     * 
     * @return string The type of Parser.
     */
    protected function getType()
    : string {
        return 'yaml';
    }
    
    /**
     * @covers adamblake\parse\Parser\Yaml::parse
     */
    public function testParseEmptyReturnsEmptyArray()
    {
        $actual = Yaml::parse('');
        $this->assertInternalType('array', $actual);
        $this->assertEmpty($actual);
    }

    /**
     * @covers adamblake\parse\Parser\Yaml::parse
     */
    public function testParsingInvalidStringThrowsParseException()
    {
        $this->expectException(ParseException::class);
        Yaml::parse('[');
    }
    
    /**
     * @covers adamblake\parse\Parser\Yaml::parse
     */
    public function testParseValid()
    {
        $actual = Yaml::parse("gloss:\n  title: S\n  type:\n  - GML\n  - XML");
        $expected = ['gloss' => ['title' => 'S', 'type' => ["GML", "XML"]]];
        $this->assertEquals($expected, $actual);
    }
}
