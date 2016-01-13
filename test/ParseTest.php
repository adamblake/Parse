<?php

namespace adamblake;

/**
 * PHPUnit test class for Parse. Files utilized by this class can be found
 * in test/files.
 */
class ParseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Parse
     */
    protected $object;

    /**
     * @var array
     */
    protected $testData;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->files = dirname(__FILE__).'/files';

        $this->testData = array(
            'zero' => array(
                'id' => '0001',
                'type' => 'donut',
                'name' => 'Cake',
                'ppu' => 0.55,
                'batters' => array(
                    'batter' => array(
                        array('id' => '1001', 'type' => 'Regular'),
                        array('id' => '1002', 'type' => 'Chocolate'),
                        array('id' => '1003', 'type' => 'Blueberry'),
                        array('id' => '1004', 'type' => "Devil's Food"),
                    ),
                ),
                'topping' => array(
                    array('id' => '5001', 'type' => 'None'),
                    array('id' => '5002', 'type' => 'Glazed'),
                    array('id' => '5005', 'type' => 'Sugar'),
                    array('id' => '5007', 'type' => 'Powdered Sugar'),
                    array('id' => '5006', 'type' => 'Chocolate with Sprinkles'),
                    array('id' => '5003', 'type' => 'Chocolate'),
                    array('id' => '5004', 'type' => 'Maple'),
                ),
            ),
            'one' => array(
                'id' => '0002',
                'type' => 'donut',
                'name' => 'Raised',
                'ppu' => 0.55,
                'batters' => array(
                    'batter' => array(
                        array('id' => '1001', 'type' => 'Regular'),
                    ),
                ),
                'topping' => array(
                    array('id' => '5001', 'type' => 'None'),
                    array('id' => '5002', 'type' => 'Glazed'),
                    array('id' => '5005', 'type' => 'Sugar'),
                    array('id' => '5003', 'type' => 'Chocolate'),
                    array('id' => '5004', 'type' => 'Maple'),
                ),
            ),
            'two' => array(
                'id' => '0003',
                'type' => 'donut',
                'name' => 'Old Fashioned',
                'ppu' => 0.55,
                'batters' => array(
                    'batter' => array(
                        array('id' => '1001', 'type' => 'Regular'),
                        array('id' => '1002', 'type' => 'Chocolate'),
                    ),
                ),
                'topping' => array(
                    array('id' => '5001', 'type' => 'None'),
                    array('id' => '5002', 'type' => 'Glazed'),
                    array('id' => '5003', 'type' => 'Chocolate'),
                    array('id' => '5004', 'type' => 'Maple'),
                ),
            ),
        );
    }

    /**
     * @covers adamblake\Parse::parse
     */
    public function testParse()
    {
        $this->assertEquals($this->testData, Parse::parse($this->files.'/valid.yml'));
        $this->assertEquals($this->testData, Parse::parse($this->files.'/valid.yaml'));
        $this->assertEquals($this->testData, Parse::parse($this->files.'/valid.json'));
        $this->assertEquals($this->testData, Parse::parse($this->files.'/valid.ini'));
    }

    /**
     * @covers adamblake\Parse::testParse
     * @expectedException adamblake\ParseException
     */
    public function testParseUnsupported()
    {
        Parse::parse($this->files.'/unsupported.conf');
    }

    /**
     * @covers adamblake\Parse::yaml
     *
     * @todo     Implement testYaml() invalid tests.
     */
    public function testYaml()
    {
        $this->assertEquals($this->testData, Parse::yaml($this->files.'/valid.yml'));
    }

    /**
     * @covers adamblake\Parse::yaml
     */
    public function testYamlEmpty()
    {
        $this->assertEmpty(Parse::yaml($this->files.'/empty.yml'));
    }

    /**
     * @covers adamblake\Parse::yaml
     * @expectedException adamblake\ParseException
     */
    public function testYamlInvalid()
    {
        Parse::yaml($this->files.'/invalid.yml');
    }

    /**
     * @covers adamblake\Parse::json
     */
    public function testJson()
    {
        $this->assertEquals($this->testData, Parse::json($this->files.'/valid.json'));
    }

    /**
     * @covers adamblake\Parse::json
     */
    public function testJsonEmpty()
    {
        $this->assertEmpty(Parse::json($this->files.'/empty.json'));
    }

    /**
     * @covers adamblake\Parse::json
     * @expectedException adamblake\ParseException
     */
    public function testJsonInvalid()
    {
        Parse::json($this->files.'/invalid.json');
    }

    /**
     * @covers adamblake\Parse::ini
     */
    public function testIni()
    {
        $this->assertEquals($this->testData, Parse::ini($this->files.'/valid.ini'));
    }

    /**
     * @covers adamblake\Parse::ini
     */
    public function testIniEmpty()
    {
        $this->assertEmpty(Parse::ini($this->files.'/empty.ini'));
    }

    /**
     * @covers adamblake\Parse::ini
     * @expectedException adamblake\ParseException
     */
    public function testIniInvalid()
    {
        Parse::ini($this->files.'/invalid.ini');
    }

    /**
     * @covers adamblake\Parse::arrayToObject
     */
    public function testArrayToObject()
    {
        $obj = Parse::arrayToObject($this->testData);
        $this->assertInstanceOf('stdClass', $obj);
        $this->assertEquals($this->testData['zero'], $obj->zero);
        $this->assertEquals($this->testData['one'], $obj->one);
        $this->assertEquals($this->testData['two'], $obj->two);
    }

    /**
     * @covers adamblake\Parse::fget_contents
     */
    public function testFileGetContents()
    {
        $expected = file_get_contents($this->files.'/valid.json');
        $this->assertEquals($expected, Parse::fileGetContents($this->files.'/valid.json'));
    }

    /**
     * @covers adamblake\Parse::fileGetContents
     * @expectedException adamblake\ParseException
     */
    public function testFileGetContentsNonexistent()
    {
        Parse::fileGetContents($this->files.'/nonexistent.file');
    }

    /**
     * @covers adamblake\Parse::parseIniString
     */
    public function testParseIniString()
    {
        $test = Parse::parseIniString('key=value');
        $this->assertEquals(array('key' => 'value'), $test);
    }

    /**
     * @covers adamblake\Parse::parseIniString
     * @expectedException adamblake\ParseException
     */
    public function testParseIniStringInvalid()
    {
        Parse::parseIniString('[');
    }

    /**
     * @covers adamblake\Parse::getExt
     */
    public function testGetExt()
    {
        $this->assertEquals('txt', Parse::getExt('simple.txt'));
        $this->assertEquals('yml', Parse::getExt('complext.txt.ini.yml'));
    }

    /**
     * @covers adamblake\Parse::detectEol
     */
    public function testDetectEol()
    {
        $rnEOL = "This is some dummy text.\r\nThis is some dummy text.\r\nThis is some dummy text.\r\nThis is some dum";
        $nEOL = "This is some dummy text.\nThis is some dummy text.\nThis is some dummy text.\nThis is some dummy tex";
        $phpEOL = 'This is some dummy text.'.PHP_EOL.'This is some dummy text.'.PHP_EOL.'This is some dummy text.'.PHP_EOL.'This is some dummy tex';

        $this->assertEquals("\r\n", Parse::detectEol($rnEOL));
        $this->assertEquals("\n", Parse::detectEol($nEOL));
        $this->assertEquals(PHP_EOL, Parse::detectEol($phpEOL));
    }
}
