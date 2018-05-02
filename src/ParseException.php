<?php

/**
 * ParseException class.
 *
 * @author Adam Blake <theadamattack@gmail.com>
 * @copyright (C) 2014 Adam Blake <theadamattack@gmail.com>
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace adamblake\parse;

/**
 * Simple Exception class that throws ErrorExceptions.
 *
 * @author Adam Blake <theadamattack@gmail.com>
 * @copyright (C) 2014 Adam Blake <theadamattack@gmail.com>
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
class ParseException extends \ErrorException
{
    /**
     * Constructor.
     *
     * @param string     $message  The Exception message to throw.
     * @param int        $code     The Exception code.
     * @param int        $severity The severity level of the exception.
     * @param string     $filename The filename where the exception is thrown.
     * @param int        $line     The line number where the exception is thrown.
     * @param \Exception $prev     The previous exception --- used for exception chaining.
     *
     * @throws ParseException Exception thrown with default message if none passed.
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        int $severity = 1,
        string $filename = __FILE__,
        int $line = __LINE__,
        \Exception $prev = null
    ) {
        if (!$message) {
            throw new ParseException('Unknown '.get_class($this));
        }

        parent::__construct($message, $code, $severity, $filename, $line, $prev);
    }
}
