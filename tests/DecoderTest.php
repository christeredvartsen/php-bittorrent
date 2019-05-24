<?php declare(strict_types=1);
namespace BitTorrent;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass BitTorrent\Decoder
 */
class DecoderTest extends TestCase {
    private $decoder;

    /**
     * Set up the decoder
     */
    public function setUp() {
        $this->decoder = new Decoder();
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getDecodeIntegerData() : array {
        return [
            ['i1e', 1],
            ['i-1e', -1],
            ['i0e', 0],
        ];
    }

    /**
     * @dataProvider getDecodeIntegerData
     * @covers ::decodeInteger
     * @param string $encoded
     * @param int $value
     */
    public function testDecoderInteger(string $encoded, int $value) {
        $this->assertEquals($value, $this->decoder->decodeInteger($encoded));
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getDecodeInvalidIntegerData() : array {
        return [
            ['i01e'],
            ['i-01e'],
            ['ifoobare'],
        ];
    }

    /**
     * @dataProvider getDecodeInvalidIntegerData
     * @covers ::decodeInteger
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid integer value.
     * @param string $value
     */
    public function testDecodeInvalidInteger(string $value) {
        $this->decoder->decodeInteger($value);
    }

    /**
     * @covers ::decodeInteger
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid integer. Integers must start wth "i" and end with "e".
     */
    public function testDecodeStringAsInteger() {
        $this->decoder->decodeInteger('4:spam');
    }

    /**
     * @covers ::decodeInteger
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid integer. Integers must start wth "i" and end with "e".
     */
    public function testDecodePartialInteger() {
        $this->decoder->decodeInteger('i10');
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getDecodeStringData() : array {
        return [
            ['4:spam', 'spam'],
            ['11:test string', 'test string'],
            ['3:foobar', 'foo'],
        ];
    }

    /**
     * @dataProvider getDecodeStringData
     * @covers ::decodeString
     * @param string $encoded
     * @param string $value
     */
    public function testDecodeString(string $encoded, string $value) {
        $this->assertSame($value, $this->decoder->decodeString($encoded));
    }

    /**
     * @covers ::decodeString
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid string. Strings consist of two parts separated by ":".
     */
    public function testDecodeInvalidString() {
        $this->decoder->decodeString('4spam');
    }

    /**
     * @covers ::decodeString
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The length of the string does not match the prefix of the encoded data.
     */
    public function testDecodeStringWithInvalidLength() {
        $this->decoder->decodeString('6:spam');
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getDecodeListData() : array {
        return [
            ['li1ei2ei3ee', [1, 2, 3]],
        ];
    }

    /**
     * @dataProvider getDecodeListData
     * @covers ::decodeList
     * @param string $encoded
     * @param array $value
     */
    public function testDecodeList(string $encoded, array $value) {
        $this->assertEquals($value, $this->decoder->decodeList($encoded));
    }

    /**
     * @covers ::decodeList
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Parameter is not an encoded list.
     */
    public function testDecodeInvalidList() {
        $this->decoder->decodeList('4:spam');
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getDecodeDictionaryData() : array {
        return [
            ['d3:foo3:bar4:spam4:eggse', ['foo' => 'bar', 'spam' => 'eggs']],
        ];
    }

    /**
     * @dataProvider getDecodeDictionaryData
     * @covers ::decodeDictionary
     * @param string $encoded
     * @param array $value
     */
    public function testDecodeDictionary(string $encoded, array $value) {
        $this->assertSame($value, $this->decoder->decodeDictionary($encoded));
    }

    /**
     * @covers ::decodeDictionary
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Parameter is not an encoded dictionary.
     */
    public function testDecodeInvalidDictionary() {
        $this->decoder->decodeDictionary('4:spam');
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getGenericDecodeData() : array {
        return [
            ['i1e', 1],
            ['4:spam', 'spam'],
            ['li1ei2ei3ee', [1, 2, 3]],
            ['d3:foo3:bare', ['foo' => 'bar']],
        ];
    }

    /**
     * @dataProvider getGenericDecodeData
     * @covers ::__construct
     * @covers ::decode
     * @param string $encoded
     * @param int|string|array $value
     */
    public function testGenericDecode(string $encoded, $value) {
        $this->assertEquals($value, $this->decoder->decode($encoded));
    }

    /**
     * @covers ::decode
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Parameter is not correctly encoded.
     */
    public function testGenericDecodeWithInvalidData() {
        $this->decoder->decode('foo');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing or empty "announce" key.
     * @covers ::decodeFile
     */
    public function testDecodeTorrentFileStrictWithMissingAnnounce() {
        $this->decoder->decodeFile(__DIR__ . '/_files/testMissingAnnounce.torrent', true);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing or empty "info" key.
     * @covers ::decodeFile
     */
    public function testDecodeTorrentFileStrictWithMissingInfo() {
        $this->decoder->decodeFile(__DIR__ . '/_files/testMissingInfo.torrent', true);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage File
     * @covers ::decodeFile
     */
    public function testDecodeNonReadableFile() {
        $this->decoder->decodeFile(__DIR__ . '/nonExistingFile');
    }

    /**
     * @covers ::decodeFile
     */
    public function testDecodeFileWithStrictChecksEnabled() {
        $list = $this->decoder->decodeFile(__DIR__ . '/_files/valid.torrent', true);

        $this->assertInternalType('array', $list);
        $this->assertArrayHasKey('announce', $list);
        $this->assertSame('http://trackerurl', $list['announce']);
        $this->assertArrayHasKey('comment', $list);
        $this->assertSame('This is a comment', $list['comment']);
        $this->assertArrayHasKey('creation date', $list);
        $this->assertEquals(1323713688, $list['creation date']);
        $this->assertArrayHasKey('info', $list);
        $this->assertInternalType('array', $list['info']);
        $this->assertArrayHasKey('files', $list['info']);
        $this->assertSame(5, count($list['info']['files']));
        $this->assertArrayHasKey('name', $list['info']);
        $this->assertSame('PHP', $list['info']['name']);
    }
}
