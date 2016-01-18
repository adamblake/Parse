<?php

namespace adamblake\Parse\Parser;

class IniTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The object to test.
     * @var Json
     */
    protected $object;

    /**
     * The directory where the supplemental test files are located
     * @var string
     */
    protected $filesDir;

    /**
     * The array of filenames for the supplemental test files.
     * @var array
     */
    protected $files;

    /**
     * The array of data in the 'valid' test file.
     * @var array
     */
    protected $data;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = 'Ini';

        $this->filesDir = dirname(__FILE__).'/files/ini';

        $this->files = array(
            'valid' => $this->filesDir.'/valid',
            'empty' => $this->filesDir.'/empty',
            'invalid' => $this->filesDir.'/invalid',
        );

        $this->data = array(
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
     * Calls the object's parse method with the contents of the passed file.
     *
     * @param string $filename The file to parse.
     *
     * @return string The array of the parsed file contents.
     */
    protected function parse($filename)
    {
        $string = file_get_contents($filename);
        $method = __NAMESPACE__.'\\'.$this->object.'::parse';

        return call_user_func_array($method, [$string]);
    }

    /**
     * @covers adamblake\Parse::parseIniString
     */
    public function testParseIniString()
    {
        $test = Ini::parseIniString('key=value');
        $this->assertEquals(array('key' => 'value'), $test);
    }

    /**
     * @covers adamblake\Parse::parseIniString
     * @expectedException adamblake\Parse\ParseException
     */
    public function testParseIniStringInvalid()
    {
        Ini::parseIniString('[');
    }

    /**
     * @covers adamblake\Parse\Ini::parse
     */
    public function testParseValid()
    {
        $actual = $this->parse($this->files['valid']);

        $this->assertEquals($this->data, $actual);
    }

    /**
     * @covers adamblake\Parse\Ini::parse
     */
    public function testParseEmpty()
    {
        $this->assertEmpty($this->parse($this->files['empty']));
    }

    /**
     * @covers adamblake\Parse\Ini::parse
     * @expectedException adamblake\Parse\ParseException
     */
    public function testParseInvalid()
    {
        $this->parse($this->files['invalid']);
    }
}
