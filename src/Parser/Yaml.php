<?php

/**
 * Yaml parser class.
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 * @copyright (C) 2016 Adam Blake <adamblake@g.ucla.edu>
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

namespace adamblake\Parse\Parser;

use adamblake\Parse\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Parses YAML strings to array.
 *
 * @author Adam Blake <adamblake@g.ucla.edu>
 * @copyright (C) 2016 Adam Blake <adamblake@g.ucla.edu>
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
class Yaml implements IParser
{
    /**
     * {@inheritdoc}
     *
     * @param string $string The string of data to parse.
     *
     * @return array The parsed data.
     *
     * @throws adamblake\Parse\ParseException Throws an exception if the string is invalid.
     */
    public static function parse($string)
    {
        $yaml = Yaml::parse(trim($string));

        if (null === $yaml) {
            // empty file
            $yaml = [];
        }

        if (!is_array($yaml)) {
            // not an array
            throw new ParseException(sprintf('The input "%s" must '
                .'contain or be a valid YAML structure.', $string));
        }

        return $yaml;
    }
}