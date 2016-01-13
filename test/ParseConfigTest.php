<?php

namespace adamblake;

/**
 * PHPUnit test class for ParseConfig. Files utilized by this class can be found
 * in test/files.
 */
class ParseConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParseConfig
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
     * @covers adamblake\ParseConfig::parse
     */
    public function testParse()
    {
        $this->assertEquals($this->testData, ParseConfig::parse($this->files.'/valid.yml'));
        $this->assertEquals($this->testData, ParseConfig::parse($this->files.'/valid.yaml'));
        $this->assertEquals($this->testData, ParseConfig::parse($this->files.'/valid.json'));
        $this->assertEquals($this->testData, ParseConfig::parse($this->files.'/valid.ini'));
    }

    /**
     * @covers adamblake\ParseConfig::testParse
     * @expectedException adamblake\ParseConfigException
     */
    public function testParseUnsupported()
    {
        ParseConfig::parse($this->files.'/unsupported.conf');
    }

    /**
     * @covers adamblake\ParseConfig::yaml
     *
     * @todo     Implement testYaml() invalid tests.
     */
    public function testYaml()
    {
        $this->assertEquals($this->testData, ParseConfig::yaml($this->files.'/valid.yml'));
    }

    /**
     * @covers adamblake\ParseConfig::yaml
     */
    public function testYamlEmpty()
    {
        $this->assertEmpty(ParseConfig::yaml($this->files.'/empty.yml'));
    }

    /**
     * @covers adamblake\ParseConfig::yaml
     * @expectedException adamblake\ParseConfigException
     */
    public function testYamlInvalid()
    {
        ParseConfig::yaml($this->files.'/invalid.yml');
    }

    /**
     * @covers adamblake\ParseConfig::json
     */
    public function testJson()
    {
        $this->assertEquals($this->testData, ParseConfig::json($this->files.'/valid.json'));
    }

    /**
     * @covers adamblake\ParseConfig::json
     */
    public function testJsonEmpty()
    {
        $this->assertEmpty(ParseConfig::json($this->files.'/empty.json'));
    }

    /**
     * @covers adamblake\ParseConfig::json
     * @expectedException adamblake\ParseConfigException
     */
    public function testJsonInvalid()
    {
        ParseConfig::json($this->files.'/invalid.json');
    }

    /**
     * @covers adamblake\ParseConfig::ini
     */
    public function testIni()
    {
        $this->assertEquals($this->testData, ParseConfig::ini($this->files.'/valid.ini'));
    }

    /**
     * @covers adamblake\ParseConfig::ini
     */
    public function testIniEmpty()
    {
        $this->assertEmpty(ParseConfig::ini($this->files.'/empty.ini'));
    }

    /**
     * @covers adamblake\ParseConfig::ini
     * @expectedException adamblake\ParseConfigException
     */
    public function testIniInvalid()
    {
        ParseConfig::ini($this->files.'/invalid.ini');
    }

    /**
     * @covers adamblake\ParseConfig::arrayToObject
     */
    public function testArrayToObject()
    {
        $obj = ParseConfig::arrayToObject($this->testData);
        $this->assertInstanceOf('stdClass', $obj);
        $this->assertEquals($this->testData['zero'], $obj->zero);
        $this->assertEquals($this->testData['one'], $obj->one);
        $this->assertEquals($this->testData['two'], $obj->two);
    }

    /**
     * @covers adamblake\ParseConfig::fget_contents
     */
    public function testFileGetContents()
    {
        $expected = file_get_contents($this->files.'/valid.json');
        $this->assertEquals($expected, ParseConfig::fileGetContents($this->files.'/valid.json'));
    }

    /**
     * @covers adamblake\ParseConfig::fileGetContents
     * @expectedException adamblake\ParseConfigException
     */
    public function testFileGetContentsNonexistent()
    {
        ParseConfig::fileGetContents($this->files.'/nonexistent.file');
    }

    /**
     * @covers adamblake\ParseConfig::parseIniString
     */
    public function testParseIniString()
    {
        $test = ParseConfig::parseIniString('key=value');
        $this->assertEquals(array('key' => 'value'), $test);
    }

    /**
     * @covers adamblake\ParseConfig::parseIniString
     * @expectedException adamblake\ParseConfigException
     */
    public function testParseIniStringInvalid()
    {
        ParseConfig::parseIniString('[');
    }

    /**
     * @covers adamblake\ParseConfig::getExt
     */
    public function testGetExt()
    {
        $this->assertEquals('txt', ParseConfig::getExt('simple.txt'));
        $this->assertEquals('yml', ParseConfig::getExt('complext.txt.ini.yml'));
    }

    /**
     * @covers adamblake\ParseConfig::detectEol
     */
    public function testDetectEol()
    {
        $rnEOL = "This is some dummy text.\r\nThis is some dummy text.\r\nThis is some dummy text.\r\nThis is some dum";
        $nEOL = "This is some dummy text.\nThis is some dummy text.\nThis is some dummy text.\nThis is some dummy tex";
        $phpEOL = 'This is some dummy text.'.PHP_EOL.'This is some dummy text.'.PHP_EOL.'This is some dummy text.'.PHP_EOL.'This is some dummy tex';

        $this->assertEquals("\r\n", ParseConfig::detectEol($rnEOL));
        $this->assertEquals("\n", ParseConfig::detectEol($nEOL));
        $this->assertEquals(PHP_EOL, ParseConfig::detectEol($phpEOL));
    }
}
