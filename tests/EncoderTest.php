<?php declare(strict_types=1);
namespace BitTorrent;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @coversDefaultClass BitTorrent\Encoder
 */
class EncoderTest extends TestCase {
    private $encoder;

    public function setUp() : void {
        $this->encoder = new Encoder();
    }

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
     */
    public function testEncodeInteger(int $value, string $encoded) : void {
        $this->assertSame($encoded, $this->encoder->encodeInteger($value));
    }

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
     */
    public function testEncodeString(string $value, string $encoded) : void {
        $this->assertSame($encoded, $this->encoder->encodeString($value));
    }

    public function getEncodeListData() : array {
        return [
            [['spam', 1, [1]], 'l4:spami1eli1eee'],
        ];
    }

    /**
     * @dataProvider getEncodeListData
     * @covers ::encodeList
     */
    public function testEncodeList(array $value, string $encoded) : void {
        $this->assertSame($encoded, $this->encoder->encodeList($value));
    }

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
     */
    public function testEncodeDictionary(array $value, string $encoded) : void {
        $this->assertSame($encoded, $this->encoder->encodeDictionary($value));
    }

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
     */
    public function testEncodeUsingGenericMethod($value, string $encoded) : void {
        $this->assertSame($encoded, $this->encoder->encode($value));
    }

    /**
     * @covers ::encode
     */
    public function testEncodeNonSupportedType() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Variables of type object can not be encoded.');
        $this->encoder->encode(new stdClass());
    }

    /**
     * @covers ::__construct
     * @covers ::encode
     */
    public function testCanEncodeEmptyArraysAsDictionaries() : void {
        $encoder = new Encoder();
        $this->assertSame('le', $encoder->encode([]));

        $encoder = new Encoder([
            'encodeEmptyArrayAsDictionary' => true,
        ]);
        $this->assertSame('de', $encoder->encode([]));
    }
}
