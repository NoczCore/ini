<?php
/**
 * This file is part of NoczCore/Ini.
 *
 * @author NoczCore <noczcore@gmail.com>
 * @link http://noczcore.github.io
 * @licence https://opensource.org/licenses/MIT
 */

namespace NoczCore\Tests\Ini;


use NoczCore\Ini\IniReader;

class IniReaderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var IniReader
     */
    private $reader;

    public function setUp()
    {
        parent::setUp();
        $this->reader = new IniReader();
    }

    public function test_readString()
    {
        $ini = <<<INI
[Section 1]
foo = "bar"
number_1 = 1
number_0 = 0
int = 10
float = 10.3
empty = ""
null_ = null
array[] = "string"
array[] = 10.3
array[] = 1
array[] = 0
[Section 2]
foo = "bar"
INI;

        $expected = [
            'Section 1' => [
                'foo' => 'bar',
                'number_1' => 1,
                'number_0' => 0,
                'int' => 10,
                'float' => 10.3,
                'empty' => "",
                'null_' => null,
                'array' => [
                    'string',
                    10.3,
                    1,
                    0
                ]
            ],
            'Section 2' => [
                'foo' => 'bar'
            ]
        ];

        $this->assertSame($expected, $this->reader->readString($ini)->toArray());
    }

    public function test_readString_shouldSubDatas()
    {
        $ini = <<<INI
[Section 1]
foo = "bar"
number_1 = 1
number_0 = 0
multiple.data.one = 1
multiple.data.two = true
multiple.data.three = null
INI;

        $expected = [
            'Section 1' => [
                'foo' => 'bar',
                'number_1' => 1,
                'number_0' => 0,
                'multiple' => [
                    'data' => [
                        'one' => 1,
                        'two' => true,
                        'three' => null
                    ]
                ]
            ]
        ];

        $this->assertSame($expected, $this->reader->readString($ini)->toArray());
    }

    public function test_readString_shouldReadBooleans()
    {
        $ini = <<<INI
bool_true_1 = on
bool_false_1 = off
bool_true_2 = true
bool_false_2 = false
bool_true_3 = yes
bool_false_3 = no
array[] = true
array[] = false
INI;
        $expected = array(
            'bool_true_1' => true,
            'bool_false_1' => false,
            'bool_true_2' => true,
            'bool_false_2' => false,
            'bool_true_3' => true,
            'bool_false_3' => false,
            'array' => array(
                true,
                false,
            ),
        );
        $this->assertSame($expected, $this->reader->readString($ini)->toArray());
    }

    public function test_readString_shouldReadNulls()
    {
        $ini = <<<INI
bar = null
array[] = null
INI;
        $expected = array(
            'bar' => null,
            'array' => array(
                null,
            ),
        );
        $this->assertSame($expected, $this->reader->readString($ini)->toArray());
    }

    public function test_readString_shouldNotDecodeQuotedStrings()
    {
        $ini = <<<INI
test1 = ""
test2 = "null"
test3 = "on"
test4 = "off"
test5 = "true"
test6 = "false"
test7 = "yes"
test8 = "no"
array[] = "true"
array[] = "false"
INI;
        $expected = array(
            'test1' => '',
            'test2' => 'null',
            'test3' => 'on',
            'test4' => 'off',
            'test5' => 'true',
            'test6' => 'false',
            'test7' => 'yes',
            'test8' => 'no',
            'array' => array(
                'true',
                'false',
            ),
        );
        $this->assertSame($expected, $this->reader->readString($ini)->toArray());
    }

    public function test_readString_withoutEmptyEndLine()
    {
        $ini = <<<INI
[Section 1]
foo = "bar"
INI;
        $expected = array(
            'Section 1' => array(
                'foo' => 'bar',
            ),
        );
        $this->assertSame($expected, $this->reader->readString($ini)->toArray());
    }

    public function test_readString_withEmptyString()
    {
        $this->assertSame(array(), $this->reader->readString('')->toArray());
    }

    public function test_readString_shouldIgnoreComments()
    {
        $expected = array(
            'Section 1' => array(
                'foo' => 'bar',
            ),
        );
        $ini = <<<INI
; <?php exit; ?> DO NOT REMOVE THIS LINE
[Section 1]
foo = "bar"
INI;
        $this->assertSame($expected, $this->reader->readString($ini)->toArray());
    }

    public function test_readString_shouldReadIniWithoutSections()
    {
        $expected = array(
            'foo' => 'bar',
        );
        $ini = <<<INI
foo = "bar"
INI;
        $this->assertSame($expected, $this->reader->readString($ini)->toArray());
    }

    public function test_readString_shouldReadSpecialCharacters()
    {
        $expected = array(
            'foo' => "&amp;6^ geagea'''&quot;;;&amp;",
        );
        $ini = <<<INI
foo = "&amp;6^ geagea'''&quot;;;&amp;"
INI;
        $this->assertSame($expected, $this->reader->readString($ini)->toArray());
    }

    public function test_readString_shouldCastToIntOnlyIfNoDataIsLost()
    {
        $ini = <<<INI
int = 10
float = 10.3
too_many_dots = 10.3.3
contains_e = 52e666
look_like_hexa = 0xf4c3b00c
look_like_binary = 0b10100111001
with_plus = +10
with_minus = -10
starts_with_zero = 0123
starts_with_zero_2 = +0123
INI;
        $expected = array(
            'int' => 10,
            'float' => 10.3,
            'too_many_dots' => '10.3.3',
            'contains_e' => '52e666',
            'look_like_hexa' => '0xf4c3b00c',
            'look_like_binary' => '0b10100111001',
            'with_plus' => '+10',
            'with_minus' => -10,
            'starts_with_zero' => '0123',
            'starts_with_zero_2' => '+0123',
        );
        $this->assertSame($expected, $this->reader->readString($ini)->toArray());
    }

    /**
     * @expectedException \NoczCore\Ini\IniReadingException
     * @expectedExceptionMessage The file /foobar doesn't exist or is not readable
     */
    public function test_readFile_shouldThrow_withInvalidFile()
    {
        $this->reader->readFile('/foobar');
    }
}
