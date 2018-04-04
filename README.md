# Parse
Simple one-off PHP class for parsing files and converting them to arrays.
Currently supports CSV, JSON, YAML (using symfony/yaml), XLSX (using phpoffice/phpspreadsheet), and INI files and 
strings. All supported types can either be read from file or from an 
appropriately formed string. All errors thrown during reading and parsing of 
files are converted to ParseExceptions and thus will not halt the flow of the 
application, and can be handled easily in try/catch blocks.

CSV files and strings are parsed according to the [RFC](https://tools.ietf.org/html/rfc4180)
and support user-defined delimiters and enclosures. It is also possible to use
the first (header) row values as keys for subsequent rows. Note that this goes 
beyond ```str_getcsv``` which does not parse the rows in a CSV string.

JSON, YAML, and INI parsing support nested arrays --- that's
right even the INIs can have nested arrays! For INIs this is indicated by using
dot syntax in sections, i.e. this
```ini
[this.section.is]
deeply = nested
```
will be parsed as 
```php
["this" => ["section" => ["is" => ["deeply" => "nested"]]]]
```

# Installing
```
> composer require adamblake/parse
```

# Usage
```php
use adamblake\parse;

// automatically determine config file type from the extension (JSON, YAML, INI)
$data = Parse::config('path/to/file');
$data['setting'] = 'value';

// parse from file as JSON, YAML, INI, CSV, XLSX
$data = Parse::json('path/to/file');
$data = Parse::yaml('path/to/file');
$data = Parse::ini('path/to/file');
$data = Parse::csv('path/to/file');
$data = Parse::xlsx('path/to/file');

// automatically determine which parser to use for config files (YAML, JSON, INI)
$data = Parse::config('path/to/file.yaml');

// parse from string input instead of file input using second parameter
$data = Parse::ini('key=value', true);

// parse string as CSV with no header and using ';' as the delimiter character 
// and '#' as the enclosure character.
$data = Parse::csv('path/to/file', true, false, ';', '#');
```

# Contributing
1. Fork it!
2. Create your feature branch: ```git checkout -b my-new-feature```
3. Commit your changes: ```git commit -am 'Add some feature'```
4. Push to the branch: ```git push origin my-new-feature```
5. Submit a [pull request](https://github.com/adamblake/SimpleDb/pulls) :D

# History
This was a project I (Adam) started writing when I was learning how to use PHP
for the first time. It was largely inspired by the fact that some functions I 
was using would throw exceptions and others would throw errors and I have a 
thing for consistency.

# License
Copyright (C) 2016-2018 Adam Blake

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

A copy of the GNU General Public License should be included along with this
program. If not, see <http://www.gnu.org/licenses/>.
