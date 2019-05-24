<?php declare(strict_types=1);
namespace BitTorrent;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @coversDefaultClass BitTorrent\Encoder
 */
class EncoderTest extends TestCase {
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
    public function getEncodeIntegerData() : array {
        return [
            [-1, 'i-1e'],
            [0, 'i0e'],
            [1, 'i1e'],
        ];
    }

    /**
     * @dataProvider getEncodeIntegerData
     * @covers ::encodeInteger
     * @param int $value
     * @param string $encoded
     */
    public function testEncodeInteger(int $value, string $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeInteger($value));
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getEncodeStringData() : array {
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
    public function testEncodeString(string $value, string $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeString($value));
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getEncodeListData() : array {
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
    public function testEncodeList(array $value, string $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeList($value));
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getEncodeDictionaryData() : array {
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
    public function testEncodeDictionary(array $value, string $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeDictionary($value));
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getEncodeData() : array {
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
     * @param string|int|array $value
     * @param string $encoded
     */
    public function testEncodeUsingGenericMethod($value, string $encoded) {
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
     */
    public function testCanEncodeEmptyArraysAsDictionaries() {
        $encoder = new Encoder();
        $this->assertSame('le', $encoder->encode([]));

        $encoder = new Encoder([
            'encodeEmptyArrayAsDictionary' => true,
        ]);
        $this->assertSame('de', $encoder->encode([]));
    }
}
