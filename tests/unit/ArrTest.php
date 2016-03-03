<?php

namespace Ohanzee\Helper;

use Codeception\Util\Stub;
use Codeception\TestCase\Test;

class ArrTest extends Test
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;

    /**
     * @covers \Ohanzee\Helper\Arr::isAssoc
     */
    public function testIsAssoc()
    {
        $arr = array('foo' => 'bar');
        $this->assertEquals(Arr::isAssoc($arr), true);
    }

    public function dataValidArrays()
    {
        return array(
            'empty'       => array(array()),
            'indexed'     => array(array(1, 2, 3)),
            'assocative'  => array(array('foo' => 'bar')),
            'ArrayObject' => array(new \ArrayObject(array('traversable' => true))),
            );
    }

    public function dataInvalidArrays()
    {
        return array(
            'boolean'  => array(true),
            'string'   => array('hello, world!'),
            'stdclass' => array(new \stdclass),
            'float'    => array(3.1418),
            'int'      => array(42),
            );
    }

    /**
     * @covers \Ohanzee\Helper\Arr::isArray
     * @dataProvider dataValidArrays
     */
    public function testIsArray($arr)
    {
        $this->assertEquals(Arr::isArray($arr), true);
    }

    /**
     * @covers \Ohanzee\Helper\Arr::isArray
     * @dataProvider dataInvalidArrays
     */
    public function testIsNotArray($arr)
    {
        $this->assertEquals(Arr::isArray($arr), false);
    }

    public function dataDeepArray()
    {
        return array(
            'deep array' => array(
                array(
                    'top' => array(
                        'level' => 'one',
                        'thing' => 'two',
                        'breakfast' => array(
                            'food' => 'eggs',
                            ),
                        'lunch' => array(
                            'food' => 'hamburger',
                            ),
                        'supper' => array(
                            'food' => 'pizza',
                            ),
                        ),
                    ),
                ),
            );
    }

    /**
     * @covers \Ohanzee\Helper\Arr::path
     * @dataProvider dataDeepArray
     */
    public function testPath($arr)
    {
        $this->assertEquals(Arr::path($arr, 'top.level'), 'one');
        $this->assertEquals(Arr::path($arr, array('top', 'level')), 'one');
        $this->assertEquals(Arr::path($arr, 'top.*.food'), array('eggs', 'hamburger', 'pizza'));
    }


    /**
     * @covers \Ohanzee\Helper\Arr::setPath
     * @dataProvider dataDeepArray
     */
    public function testSetPath($arr)
    {
        $this->assertEquals(Arr::setPath($arr, 'top.level', 'red'), null);
        $this->assertEquals($arr['top']['level'], 'red');

        $this->assertEquals(Arr::setPath($arr, 'top.breakfast.food', 'toast'), null);
        $this->assertEquals($arr['top']['breakfast']['food'], 'toast');
    }

    /**
     * @covers \Ohanzee\Helper\Arr::range()
     */
    public function testRange()
    {
        $values = array(5, 10, 15, 20);
        $this->assertEquals(Arr::range(5, 20), array_combine($values, $values));

        $values = array(1, 2, 3, 4, 5);
        $this->assertEquals(Arr::range(1, 5), array_combine($values, $values));
    }

    /**
     * @covers \Ohanzee\Helper\Arr::get()
     */
    public function testGet()
    {
        $values = array(
            'one' => 'foo',
            'two' => 'bar',
            );
        $this->assertEquals(Arr::get($values, 'one'), 'foo');
        $this->assertEquals(Arr::get($values, 'two'), 'bar');

        $default = false;
        $this->assertEquals(Arr::get($values, 'fail', $default), $default);
    }
    /**
     * @covers \Ohanzee\Helper\Arr::extract()
     * @dataProvider dataDeepArray
     */
    public function testExtract($arr)
    {
        $simple = array(
            'foo' => 'bar',
            );
        $this->assertEquals(Arr::extract($simple, array_keys($simple)), $simple);

        $default = false;
        $paths = array(
            'breakfast.food',
            'lunch.food',
            'supper.food',
            'fake.key',
            );
        $expect = array(
            'breakfast' => array(
                'food' => 'eggs',
                ),
            'lunch' => array(
                'food' => 'hamburger',
                ),
            'supper' => array(
                'food' => 'pizza',
                ),
            'fake' => array(
                'key' => $default,
                ),
            );
        $this->assertEquals(Arr::extract($arr['top'], $paths, $default), $expect);
    }

    public function dataMappedContact()
    {
        return [
            [
                [
                    [
                        'name'  => 'fname',
                        'value' => 'Sherlock',
                    ],
                    [
                        'name'  => 'lname',
                        'value' => 'Holmes',
                    ],
                    [
                        'name'  => 'street',
                        'value' => 'Baker',
                    ],
                    [
                        'name'  => 'house',
                        'value' => '221B',
                    ],
                ],
            ],
        ];
    }

    public function dataMappedContactRecursive()
    {
        return [
            [
                [
                    [
                        'name'  => 'fname',
                        'value' => 'Sherlock',
                    ],
                    [
                        'name'  => 'lname',
                        'value' => 'Holmes',
                    ],
                    [
                        'name'  => 'address',
                        'value' => [
                            [
                                'name'  => 'street',
                                'value' => 'Baker',
                            ],
                            [
                                'name'  => 'house',
                                'value' => '221B',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function dataMappedContactDifferent()
    {
        return [
            [
                [
                    [
                        'field' => 'fname',
                        'val'   => 'Sherlock',
                    ],
                    [
                        'field' => 'lname',
                        'val'   => 'Holmes',
                    ],
                    [
                        'field' => 'street',
                        'val'   => 'Baker',
                    ],
                    [
                        'field' => 'house',
                        'val'   => '221B',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataMappedContact
     */
    public function testFromMapping($arr)
    {
        $expected = [
            'fname'  => 'Sherlock',
            'lname'  => 'Holmes',
            'street' => 'Baker',
            'house'  => '221B',
        ];
        $this->assertEquals($expected, Arr::fromMapping($arr));
//        $this->assertEquals($expected, $arr);
    }

    /**
     * @dataProvider dataMappedContact
     */
    public function testFromMappingEmpty($arr)
    {
        $expected = [];
        $recursive = false;
        $key = 'key';
        $this->assertEquals($expected, Arr::fromMapping($arr, $recursive, $key));
    }

    /**
     * @dataProvider dataMappedContactRecursive
     */
    public function testFromMappingRecursive($arr)
    {
        $expected = [
            'fname'   => 'Sherlock',
            'lname'   => 'Holmes',
            'address' => [
                'street' => 'Baker',
                'house'  => '221B',
            ],
        ];
        $recursive = true;
        $this->assertEquals($expected, Arr::fromMapping($arr, $recursive));
    }

    /**
     * @dataProvider dataMappedContactDifferent
     */
    public function testFromMappingDifferent($arr)
    {
        $expected = [
            'fname'  => 'Sherlock',
            'lname'  => 'Holmes',
            'street' => 'Baker',
            'house'  => '221B',
        ];
        $recursive = false;
        $key = 'field';
        $value = 'val';
        $this->assertEquals($expected, Arr::fromMapping($arr, $recursive, $key, $value));
    }

    public function dataRelatedContact()
    {
        return [
            [
                [
                    'fname'  => 'Sherlock',
                    'lname'  => 'Holmes',
                    'street' => 'Baker',
                    'house'  => '221B',
                ],
            ],
        ];
    }

    public function dataRelatedContactRecursive()
    {
        return [
            [
                [
                    'fname'   => 'Sherlock',
                    'lname'   => 'Holmes',
                    'address' => [
                        'street' => 'Baker',
                        'house'  => '221B',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataRelatedContact
     */
    public function testToMapping($arr)
    {
        $expected = [
            [
                'name'  => 'fname',
                'value' => 'Sherlock',
            ],
            [
                'name'  => 'lname',
                'value' => 'Holmes',
            ],
            [
                'name'  => 'street',
                'value' => 'Baker',
            ],
            [
                'name'  => 'house',
                'value' => '221B',
            ],
        ];
        $this->assertEquals($expected, Arr::toMapping($arr));
    }

    /**
     * @dataProvider dataRelatedContactRecursive
     */
    public function testToMappingRecursive($arr)
    {
        $expected = [
            [
                'name'  => 'fname',
                'value' => 'Sherlock',
            ],
            [
                'name'  => 'lname',
                'value' => 'Holmes',
            ],
            [
                'name'  => 'address',
                'value' => [
                    [
                        'name'  => 'street',
                        'value' => 'Baker',
                    ],
                    [
                        'name'  => 'house',
                        'value' => '221B',
                    ],
                ],
            ],
        ];
        $recursive = true;
        $this->assertEquals($expected, Arr::toMapping($arr, $recursive));
    }

    /**
     * @dataProvider dataRelatedContact
     */
    public function testToMappingDifferent($arr)
    {
        $expected = [
            [
                'field' => 'fname',
                'val'   => 'Sherlock',
            ],
            [
                'field' => 'lname',
                'val'   => 'Holmes',
            ],
            [
                'field' => 'street',
                'val'   => 'Baker',
            ],
            [
                'field' => 'house',
                'val'   => '221B',
            ],
        ];
        $recursive = false;
        $key = 'field';
        $value = 'val';
        $this->assertEquals($expected, Arr::toMapping($arr, $recursive, $key, $value));
    }
}
