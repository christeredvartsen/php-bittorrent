<?php
namespace BitTorrent;

use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * @coversDefaultClass BitTorrent\Encoder
 */
class EncoderTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Encdoder
     */
    private $encoder;

    /**
     * Set up the encoder
     */
    public function setUp() {
        $this->encoder = new Encoder();
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getEncodeIntegerData() {
        return [
            [-1, 'i-1e'],
            [0, 'i0e'],
            [1, 'i1e'],
        ];
    }

    /**
     * @dataProvider getEncodeIntegerData
     * @covers ::encodeInteger
     * @covers ::isInt
     * @param int $value
     * @param string $encoded
     */
    public function testEncodeInteger($value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeInteger($value));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Expected an integer.
     * @covers ::encodeInteger
     */
    public function testEncodeNonIntegerAsInteger() {
        $this->encoder->encodeInteger('one');
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getEncodeStringData() {
        return [
            ['spam', '4:spam'],
            ['foobar', '6:foobar'],
            ['foo:bar', '7:foo:bar'],
        ];
    }

    /**
     * @dataProvider getEncodeStringData
     * @covers ::encodeString
     * @param string $value
     * @param string $encoded
     */
    public function testEncodeString($value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeString($value));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Expected string, got: integer.
     * @covers ::encodeString
     */
    public function testEncodeNonStringAsString() {
        $this->encoder->encodeString(1);
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getEncodeListData() {
        return [
            [['spam', 1, [1]], 'l4:spami1eli1eee'],
        ];
    }

    /**
     * @dataProvider getEncodeListData
     * @covers ::encodeList
     * @param array $value
     * @param string $encoded
     */
    public function testEncodeList(array $value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeList($value));
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getEncodeDictionaryData() {
        return [
            [['1' => 'foo', 'foo' => 'bar', 'list' => [1, 2, 3]], 'd3:foo3:bar4:listli1ei2ei3ee1:13:fooe'],
            [['foo' => 'bar', 'spam' => 'eggs'], 'd3:foo3:bar4:spam4:eggse'],
            [['spam' => 'eggs', 'foo' => 'bar'], 'd3:foo3:bar4:spam4:eggse'],
        ];
    }

    /**
     * @dataProvider getEncodeDictionaryData
     * @covers ::encodeDictionary
     * @param array $value
     * @param string $encoded
     */
    public function testEncodeDictionary(array $value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeDictionary($value));
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getEncodeData() {
        return [
            [1, 'i1e'],
            ['spam', '4:spam'],
            [[1, 2, 3], 'li1ei2ei3ee'],
            [['foo' => 'bar', 'spam' => 'sucks'], 'd3:foo3:bar4:spam5:suckse'],
        ];
    }

    /**
     * @dataProvider getEncodeData
     * @covers ::encode
     * @covers ::isInt
     * @param string|int|array $value
     * @param string $encoded
     */
    public function testEncodeUsingGenericMethod($value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encode($value));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Variables of type object can not be encoded.
     * @covers ::encode
     */
    public function testEncodeNonSupportedType() {
        $this->encoder->encode(new stdClass());
    }

    /**
     * @covers ::__construct
     * @covers ::encode
     * @covers ::setParam
     */
    public function testCanEncodeEmptyArraysAsDictionaries() {
        $this->assertSame('le', $this->encoder->encode([]));
        $this->assertSame($this->encoder, $this->encoder->setParam('encodeEmptyArrayAsDictionary', true));
        $this->assertSame('de', $this->encoder->encode([]));
        $this->assertSame($this->encoder, $this->encoder->setParam('encodeEmptyArrayAsDictionary', false));
        $this->assertSame('le', $this->encoder->encode([]));
    }
}
