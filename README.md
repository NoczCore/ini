# ini
Read and write INI files/strings in PHP.

[![Travis branch](https://img.shields.io/travis/rust-lang/rust/master.svg?style=flat-square)](https://travis-ci.org/NoczCore/ini)
[![Packagist](https://img.shields.io/packagist/dt/noczcore/ini.svg?style=flat-square)](https://packagist.org/packages/noczcore/ini)
[![Licence](https://img.shields.io/packagist/l/noczcore/ini.svg?style=flat-square)](https://raw.githubusercontent.com/NoczCore/ini/master/LICENSE)
[![HHVM (branch)](https://img.shields.io/hhvm/noczcore/ini/master.svg?style=flat-square)](https://travis-ci.org/NoczCore/ini)

Inspired of [Piwik/ini](https://github.com/piwik/component-ini/)

## Requirements

- PHP >= 5.3.3
- parse_ini_string is enabled in php.ini

## Installation

```json
composer require noczcore/ini
```

## Features

- Read INI files and INI strings
- Write INI files and INI strings
- Return a collection instead of a simple array
- Throws exceptions instead of PHP errors
- Better type supports ([Exemple](https://github.com/NoczCore/ini#read)):
    * Parse boolean (true/false, on/off, yes/no) to real PHP boolean
    * Parse null to real PHP null
    * Parse int/float to real PHP int/float
- Advanced parser ([Exemple](https://github.com/NoczCore/ini#read)):
    * A key with "[]" has an array value with many values
    * A key with "." has an recursive array value with many values

## Usage:

### Read

```php
use \NoczCore\Ini\IniReader;
$reader = new IniReader();

$string = <<<INI
[Section 1]
foo = "bar"
number_1 = 1
number_0 = 0
int = 10
float = 10.3
empty = ""
null_ = null
boolean_1 = true
boolean_0 = false
array[] = "string"
array[] = 10.3
array[] = 1
array[] = 0
names.users[] = "NoczCore"
names.administators[] = "John Doe"
names.administators[] = "Jane Doe"
[Section 2]
foo = "bar"
INI;

// Read a string
$array = $reader->readString($string);
// Read a file
$array = $reader->readFile('config.ini');

var_dump($array);
```

####Return:
```
Array
(
    [Section 1] => Array
        (
            [foo] => bar
            [number_1] => 1
            [number_0] => 0
            [int] => 10
            [float] => 10.3
            [empty] => ""
            [null_] => null
            [boolean_1] => true
            [boolean_0] => false
            [array] => Array
                (
                    [0] => string
                    [1] => 10.3
                    [2] => 1
                    [3] => 0
                )

            [names] => Array
                (
                    [users] => Array
                        (
                            [0] => NoczCore
                        )

                    [administators] => Array
                        (
                            [0] => John Doe
                            [1] => Jane Doe
                        )

                )

        )

    [Section 2] => Array
        (
            [foo] => bar
        )

)
```

### Write

```php
use \NoczCore\Ini\IniWriter;
$writer = new IniWriter();

// Write to a string
$string = $writer->writeToString($array);
// Write to a file
$writer->writeToFile('config.ini', $array);
```

## License

The Ini component is released under the [MIT](https://raw.githubusercontent.com/NoczCore/ini/master/LICENSE).

## Contributing

To run the unit tests:

```
vendor/bin/phpunit
```