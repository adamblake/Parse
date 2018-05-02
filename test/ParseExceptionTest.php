<?php

/**
 * PHPUnit test class for the ParseException class.
 */

namespace adamblake\parse;

/**
 * Tests the behavior of the ParseException class.
 *
 * @author Adam Blake <theadamattack@gmail.com>
 * @copyright (c) 2016, Adam Blake <theadamattack@gmail.com>
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License (GPL-3.0)
 */
class ParseExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers adamblake\parse\ParseException::__construct
     */
    public function testNoErrorMessageReturnsDefaultMessage()
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Unknown ' . ParseException::class);
        throw new ParseException();
    }
    
    /**
     * @covers adamblake\parse\ParseException::__construct
     */
    public function testParseExceptionProperlyRelaysExceptionCode()
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionCode(2);
        throw new ParseException('message', 2);
    }
}
