# ParseConfig
Simple one-off PHP class for parsing config files and converting them to arrays.
Currently supports JSON, YAML (using symfony/yaml), and INI files. INI parsing 
also supports nested arrays by using dot syntax in sections, i.e. this
```ini
[this.section.is]
deeply = nested
```
will be parsed as 
```php
["this" => ["section" => ["is" => ["deeply" => "nested"]]]]
```

All config files can either be read from file or from an appropriately formed
string. Additionally, the data can be returned as an associative array or 
a series of nested objects, as an added convenience for those who want 
consistent structure in their applications and need one or the other. (See the 
Usage section below for more details.)

Finally, another useful feature of this packages is that all errors thrown 
during reading and parsing of files are converted to ParseConfigExceptions and
thus will not halt the flow of the application and can be handled easily in
try/catch blocks.

# Installing
This class is not on Packagist. Either download the zip and the symfony/yaml 
dependency and configure them manually, or use it with composer by adding the 
following to your composer.json:
```json
{
  "repositories": [
    {
      "type": "git",
      "url": "https://github.com/adamblake/ParseConfig"
    }
  ],
  "require": {
    "adamblake/ParseConfig": "dev-master"
  }
}
```

# Usage
```php
use adamblake\ParseConfig;
...

// automatically determine file type from the extension
$data = ParseConfig::parse('path/to/file');
$data['setting'] = 'value';

// parse as json, yaml, ini
$data = ParseConfig::json('path/to/file');
$data = ParseConfig::yaml('path/to/file');
$data = ParseConfig::ini('path/to/file');

// read from string instead (only with named parse functions)
$data = ParseConfig::ini('key=value', true);

// convert the parsed file to an object
$obj = ParseConfig::parse('path/to/file', true);
$obj->setting = 'value';

// convert a parsed string to an object
$data = ParseConfig::ini('key=value', true);
$obj  = ParseConfig::arrayToObject($data);
$obj->key = 'value';
```

# Contributing
1. Fork it!
2. Create your feature branch: ```git checkout -b my-new-feature```
3. Commit your changes: ```git commit -am 'Add some feature'```
4. Push to the branch: ```git push origin my-new-feature```
5. Submit a pull request :D

# History
This was a project I (Adam) started writing when I was learning how to use PHP
for the first time. It was largely inspired by the fact that some functions I 
was using would throw exceptions and others would throw errors and I have a 
thing for consistency.

# License
Copyright (C) 2014 Adam Blake

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