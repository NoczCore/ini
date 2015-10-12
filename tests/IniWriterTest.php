<?php
/**
 * This file is part of NoczCore/Ini.
 *
 * @author NoczCore <noczcore@gmail.com>
 * @link http://noczcore.github.io
 * @licence https://opensource.org/licenses/MIT
 */

namespace NoczCore\Tests\Ini;


use NoczCore\Ini\IniWriter;

class IniWriterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var IniWriter
     */
    private $writer;

    public function setUp()
    {
        parent::setUp();
        $this->writer = new IniWriter();
    }

    public function test_writeToString()
    {
        $config = array(
            'Section 1' => [
                'foo' => 'bar',
                'bool_true' => true,
                'bool_false' => false,
                'int_one' => 1,
                'int_zero' => 0,
                'int' => 10,
                'float' => 10.3,
                'array' => [
                    'string',
                    10.3,
                    true,
                    false,
                    'name' => [
                        'James',
                        'Hemery',
                        'nickname' => [
                            'NoczCore',
                            'JamesHemery'
                        ],
                        'full' => "James Hemery"
                    ]
                ]
            ],
            'Section 2' => [
                'foo' => 'bar'
            ]
        );
        $expected = <<<INI
[Section 1]
foo = "bar"
bool_true = true
bool_false = false
int_one = 1
int_zero = 0
int = 10
float = 10.3
array[] = "string"
array[] = 10.3
array[] = true
array[] = false
array.name[] = "James"
array.name[] = "Hemery"
array.name.nickname[] = "NoczCore"
array.name.nickname[] = "JamesHemery"
array.name.full = "James Hemery"
[Section 2]
foo = "bar"

INI;
        $this->assertEquals($expected, $this->writer->writeToString($config));
    }

    public function test_writeToString_withEmptyConfig()
    {
        $this->assertEquals("", $this->writer->writeToString(array()));
    }

    public function test_writeToString_shouldAddHeader()
    {
        $header = "; <?php exit; ?> DO NOT REMOVE THIS LINE";
        $config = array(
            'Section 1' => array(
                'foo' => 'bar',
            ),
        );
        $expected = <<<INI
; <?php exit; ?> DO NOT REMOVE THIS LINE
[Section 1]
foo = "bar"

INI;
        $this->assertEquals($expected, $this->writer->writeToString($config, $header));
    }
}
