<?php

namespace adamblake\Parse\Parser;

class CsvTest extends \PHPUnit_Framework_TestCase
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
        $this->object = 'Csv';

        $this->filesDir = dirname(__FILE__).'/files/csv';

        $this->files = array(
            'valid' => $this->filesDir.'/valid',
            'empty' => $this->filesDir.'/empty',
            'invalid' => $this->filesDir.'/invalid',
        );

        $this->data = array(
            array('id' => '0000', 'name' => 'Adam', 'age' => '25', 'color' => 'blue', 'sentence' => 'has, a comma'),
            array('id' => '0001', 'name' => 'Brad', 'age' => '24', 'color' => 'green', 'sentence' => '""is quoted""'),
            array('id' => '0002', 'name' => 'Carl', 'age' => '26', 'color' => 'yellow', 'sentence' => ''),
            array('id' => '0003', 'name' => 'Dave', 'age' => '24', 'color' => 'green', 'sentence' => ''),
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
     * @covers adamblake\Parse\Csv::parse
     */
    public function testParseValid()
    {
        $actual = $this->parse($this->files['valid']);

        $this->assertEquals($this->data, $actual);
    }

    /**
     * @covers adamblake\Parse\Csv::parse
     */
    public function testParseEmpty()
    {
        $this->assertEmpty($this->parse($this->files['empty']));
    }

    /**
     * @covers adamblake\Parse\Csv::parse
     * @expectedException adamblake\Parse\ParseException
     */
    public function testParseInvalid()
    {
        $this->parse($this->files['invalid']);
    }
}
