<?php

namespace adamblake\parse;

/**
 * PHPUnit test class for Parse. Files utilized by this class can be found
 * in test/files.
 */
class ParseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The array of test data.
     * @var array
     */
    protected $data = [
        'zero' => [
            'id' => '0001',
            'batters' => [
                'batter' => [
                    ['id' => '1001', 'type' => 'Regular'],
                    ['id' => '1004', 'type' => "Devil's Food"],
                ],
            ],
        ],
        'one' => [
            'id' => '0002', 'ppu' => 0.55, 'topping' => [
                ['id' => '5004', 'type' => 'Maple'],
            ],
        ],
    ];

    /**
     * The directory for the test data files.
     * @var string
     */
    protected $files = __DIR__.'/test_files/';

    /**
     * @covers adamblake\parse\Parse::yaml
     * @covers adamblake\parse\Parse::getParser
     * @covers adamblake\parse\Parse::parse
     */
    public function testCanParseYamlFiles()
    {
        $this->assertEquals($this->data, Parse::yaml($this->files.'/v.yaml'));
    }

    /**
     * @covers adamblake\parse\Parse::json
     * @covers adamblake\parse\Parse::getParser
     * @covers adamblake\parse\Parse::parse
     */
    public function testCanParseJsonFiles()
    {
        $this->assertEquals($this->data, Parse::json($this->files.'/v.json'));
    }

    /**
     * @covers adamblake\parse\Parse::ini
     * @covers adamblake\parse\Parse::getParser
     * @covers adamblake\parse\Parse::parse
     */
    public function testCanParseStandardIniFiles()
    {
        $data = ['section' => ['key' => 'value']];
        $this->assertEquals($data, Parse::ini($this->files.'/std.ini'));
    }

    /**
     * @covers adamblake\parse\Parse::ini
     */
    public function testCanParseDeeplyNestedIniFiles()
    {
        $this->assertEquals($this->data, Parse::ini($this->files.'/v.ini'));
    }

    /**
     * @covers adamblake\parse\Parse::csv
     * @covers adamblake\parse\Parse::parse
     * @covers adamblake\parse\Parse::getParser
     *
     * @dataProvider validCsvFilesProvider
     *
     * @param string $file  The filename of the file to parse.
     * @param string $delim The delimiter used in the file's data.
     */
    public function testCanParseCsvWithHeaderAndDifferentDelimiters(
        string $file, string $delim
    ) {
        $data = [
            ['id' => '0', 'name' => 'Adam', 'sentence' => 'has, a comma'],
            ['id' => '1', 'name' => 'Brad', 'sentence' => '"is quoted"'],
        ];
        $this->assertEquals($data, Parse::csv($file, false, true, $delim));
    }

    /**
     * @covers adamblake\parse\Parse::tsv
     */
    public function testCanParseTabSeparatedWithShortcutFunction()
    {
        $data = [
            ['id' => '0', 'name' => 'Adam', 'sentence' => 'has, a comma'],
            ['id' => '1', 'name' => 'Brad', 'sentence' => '"is quoted"'],
        ];
        $this->assertEquals($data, Parse::tsv($this->files.'/tab.csv', false, true));
    }

    /**
     * @covers adamblake\parse\Parse::csv
     * @covers adamblake\parse\Parse::parse
     * @covers adamblake\parse\Parse::getParser
     */
    public function testCanParseCsvWithNoHeader()
    {
        $data = [
            ['id', 'name', 'sentence'],
            ['0', 'Adam', 'has, a comma'],
            ['1', 'Brad', '"is quoted"'],
        ];
        $test = Parse::csv($this->files.'/v.csv', false, false, ',');
        $this->assertEquals($data, $test);
    }

    /**
     * @covers adamblake\parse\Parse::config
     *
     * @dataProvider validConfigFilesProvider
     */
    public function testConfigCanParseValidConfigFilesToArray($file)
    {
        $this->assertEquals($this->data, Parse::config($file));
    }

    /**
     * @covers adamblake\parse\Parse::config
     */
    public function testUnsupportedConfigExtension()
    {
        $this->expectException(ParseException::class);
        Parse::config($this->files.'/unsupported.conf');
    }

    /**
     * @covers adamblake\parse\Parse::fileGetContents
     *
     * @dataProvider validConfigFilesProvider
     *
     * @param string $file The valid file to get the contents of.
     */
    public function testFileGetContentsWrapperReturnsCorrectOutput($file)
    {
        $expected = file_get_contents($file);
        $this->assertEquals($expected, Parse::fileGetContents($file));
    }

    /**
     * @covers adamblake\parse\Parse::fileGetContents
     */
    public function testFileGetContentsWrapperThrowsExceptionsNotErrors()
    {
        $this->expectException(ParseException::class);
        Parse::fileGetContents($this->files.'/dne.dne');
    }

    /**
     * @covers adamblake\parse\Parse::getExt
     */
    public function testGetExtReturnsFileExtension()
    {
        $this->assertEquals('txt', Parse::getExt('simple.txt'));
    }

    /**
     * @covers adamblake\parse\Parse::getExt
     */
    public function testGetExtReturnsFileExtensionForMultipleDotFilenames()
    {
        $this->assertEquals('yml', Parse::getExt('complext.txt.ini.yml'));
    }

    /**
     * @covers adamblake\parse\Parse::detectEol
     */
    public function testDetectEolDetectsWindowsReturns()
    {
        $rnEOL = "This is text.\r\n This is text \r\nThis is some ";
        $this->assertEquals("\r\n", Parse::detectEol($rnEOL));
    }

    /**
     * @covers adamblake\parse\Parse::detectEol
     */
    public function testDetectEolDetectsLinuxReturns()
    {
        $nEOL = "This is text.\nThis is text.\nThis is text.\nThis is text";
        $this->assertEquals("\n", Parse::detectEol($nEOL));
    }

    /**
     * @covers adamblake\parse\Parse::detectEol
     */
    public function testDetectEolDetectsLineEndingsBuiltWithPHPEOL()
    {
        $phpEOL = 'This is text.'.PHP_EOL.'This is text.'.PHP_EOL.'This is tex';
        $this->assertEquals(PHP_EOL, Parse::detectEol($phpEOL));
    }

    /**
     * @covers adamblake\parse\Parse::xlsx
     */
    public function testParseXlsxCanParseDataWithNoHeader()
    {
        $actual = Parse::xlsx($this->files.'/multipleRows.xlsx', false);
        $this->assertEquals([[1, 2], [3, 4], [5, 6]], $actual);
    }

    /**
     * @covers adamblake\parse\Parse::xlsx
     */
    public function testParseXlsxCanParseDataWithHeader()
    {
        $actual = Parse::xlsx($this->files.'/multipleRows.xlsx');
        $this->assertEquals([[1 => 3, 2 => 4], [1 => 5, 2 => 6]], $actual);
    }

    /**
     * @covers adamblake\parse\Parse::table
     */
    public function testParseTableAutomaticallyDeterminesCorrectParserXlsx()
    {
        $actual = Parse::table($this->files.'/multipleRows.xlsx', false);
        $this->assertEquals([[1, 2], [3, 4], [5, 6]], $actual);
    }

    /**
     * @covers adamblake\parse\Parse::table
     * @dataProvider validTableFilesProvider
     * @param string $filename The filename of the file to parse.
     */
    public function testParseTableParsesValidTableTypesWithNoHeader($filename)
    {
        $actual = Parse::table($filename, false);
        $this->assertEquals([[1, 2], [3, 4], [5, 6]], $actual);
    }

    /**
     * @covers adamblake\parse\Parse::table
     * @dataProvider validTableFilesProvider
     * @param string $filename The filename of the file to parse.
     */
    public function testParseTableParsesValidTableTypesWithHeader($filename)
    {
        $actual = Parse::table($filename, true);
        $this->assertEquals([[1 => 3, 2 => 4], [1 => 5, 2 => 6]], $actual);
    }

    /**
     * @covers adamblake\parse\Parse::table
     */
    public function testParseTableThrowsErrorForInvalidType()
    {
        $this->expectException(ParseException::class);
        Parse::table($this->files.'/invalid.file', false);
    }

    /**
     * Provides the filenames for files that should be parseable using the
     * Parse::config() method.
     *
     * @return array Returns the valid parseable files.
     */
    public function validConfigFilesProvider()
    : array {
        return [
            'yaml' => [$this->files.'/v.yaml'],
            'yml'  => [$this->files.'/v.yml'],
            'json' => [$this->files.'/v.json'],
            'ini'  => [$this->files.'/v.ini'],
        ];
    }

    /**
     * Provides the filenames for files that should be parseable using the
     * Parse::table() method.
     *
     * @return array Returns the valid parseable files.
     */
    public function validTableFilesProvider()
    : array {
        return [
            'csv' => [$this->files.'/multipleRows.csv'],
            'tsv'  => [$this->files.'/multipleRows.tsv'],
            'txt'  => [$this->files.'/multipleRows.txt'],
            'xlsx' => [$this->files.'/multipleRows.xlsx'],
        ];
    }

    /**
     * Provides the filenames and delimiters for CSV semi-compliant files with
     * different delimiters.
     *
     * @return array Returns the array of filenames and delimiters.
     */
    public function validCsvFilesProvider()
    : array {
        return [
            ','  => [$this->files.'/v.csv', ','],
            ';'  => [$this->files.'/semi.csv', ';'],
            '\t' => [$this->files.'/tab.csv', "\t"],
        ];
    }
}
