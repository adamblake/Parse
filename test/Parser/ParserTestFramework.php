<?php

/**
 * PHPUnit test class for the ParserTestFramework class.
 */

namespace adamblake\parse\Parser;

/**
 * Tests the behavior of the ParserTestFramework class.
 *
 * @author Adam Blake <theadamattack@gmail.com>
 * @copyright (c) 2016, Adam Blake <theadamattack@gmail.com>
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License (GPL-3.0)
 */
abstract class ParserTestFramework extends \PHPUnit\Framework\TestCase
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->files = __DIR__ . '/test_files';

        // load the class once so that debugger doesn't go through autoloader
        $class = __NAMESPACE__ . '\\' . ucfirst($this->getType());
        new $class;
    }
    
    /**
     * All Parser tests should provide the type of file they are testing---this
     * makes it easier to pre-load the class and skip that part of the debug
     * step-through.
     */
    abstract protected function getType(): string;
}
