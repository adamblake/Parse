<?php

/**
 * ParseConfigException class.
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 * @copyright (C) 2014 Adam Blake <adamblake@g.ucla.edu>
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */

/*
 * Copyright (C) 2014 Adam Blake <adamblake@g.ucla.edu>
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

namespace adamblake;

/**
 * Simple Exception class that throws ErrorExceptions.
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 * @copyright (C) 2014 Adam Blake <adamblake@g.ucla.edu>
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
class ParseConfigException extends \ErrorException
{
    /**
     * Constructor.
     *
     * @param string     $message  The Exception message to throw.
     * @param int        $code     The Exception code.
     * @param int        $severity The severity level of the exception.
     * @param string     $filename The filename where the exception is thrown.
     * @param int        $lineno   The line number where the exception is thrown.
     * @param \Exception $previous The previous exception used for the exception chaining.
     *
     * @throws ParseConfigException Exception thrown with default message if none passed.
     */
    public function __construct($message, $code = 0, $severity = 1, $filename = __FILE__,
            $lineno = __LINE__, \Exception $previous = null
    ) {
        // handle omitted message
        if (!$message) {
            throw new $this('Unknown '.get_class($this));
        }

        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
    }
}
