<?php

/**
 * Yaml parser class.
 *
 * @author Adam Blake <theadamattack@gmail.com>
 * @copyright (C) 2016 Adam Blake <theadamattack@gmail.com>
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

namespace adamblake\parse\Parser;

use adamblake\parse\ParseException;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use \Symfony\Component\Yaml\Exception\ParseException as SymfonyYamlException;

/**
 * Parses YAML strings to array.
 *
 * @author Adam Blake <theadamattack@gmail.com>
 * @copyright (C) 2016 Adam Blake <theadamattack@gmail.com>
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
class Yaml implements ParserInterface
{
    /**
     * {@inheritdoc}
     *
     * @param string $string The string of data to parse.
     *
     * @return array The parsed data.
     * @throws ParseException if the string is invalid YAML.
     */
    public static function parse(string $string): array
    {
        try {
            $yaml = SymfonyYaml::parse(trim($string));
        } catch (SymfonyYamlException $e) {
            throw new ParseException($e);
        }

        return $yaml ?? [];
    }
}
