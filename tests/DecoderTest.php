<?php declare(strict_types=1);
namespace BitTorrent;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass BitTorrent\Decoder
 */
class DecoderTest extends TestCase {
    private $decoder;

    public function setUp() : void {
        $this->decoder = new Decoder();
    }

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
     */
    public function testDecoderInteger(string $encoded, int $value) : void {
        $this->assertEquals($value, $this->decoder->decodeInteger($encoded));
    }

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
     */
    public function testDecodeInvalidInteger(string $value) : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid integer value.');
        $this->decoder->decodeInteger($value);
    }

    /**
     * @covers ::decodeInteger
     */
    public function testDecodeStringAsInteger() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid integer. Integers must start wth "i" and end with "e".');
        $this->decoder->decodeInteger('4:spam');
    }

    /**
     * @covers ::decodeInteger
     */
    public function testDecodePartialInteger() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid integer. Integers must start wth "i" and end with "e".');
        $this->decoder->decodeInteger('i10');
    }

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
     */
    public function testDecodeString(string $encoded, string $value) : void {
        $this->assertSame($value, $this->decoder->decodeString($encoded));
    }

    /**
     * @covers ::decodeString
     */
    public function testDecodeInvalidString() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid string. Strings consist of two parts separated by ":".');
        $this->decoder->decodeString('4spam');
    }

    /**
     * @covers ::decodeString
     */
    public function testDecodeStringWithInvalidLength() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The length of the string does not match the prefix of the encoded data.');
        $this->decoder->decodeString('6:spam');
    }

    public function getDecodeListData() : array {
        return [
            ['li1ei2ei3ee', [1, 2, 3]],
        ];
    }

    /**
     * @dataProvider getDecodeListData
     * @covers ::decodeList
     */
    public function testDecodeList(string $encoded, array $value) : void {
        $this->assertEquals($value, $this->decoder->decodeList($encoded));
    }

    /**
     * @covers ::decodeList
     */
    public function testDecodeInvalidList() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter is not an encoded list.');
        $this->decoder->decodeList('4:spam');
    }

    public function getDecodeDictionaryData() : array {
        return [
            ['d3:foo3:bar4:spam4:eggse', ['foo' => 'bar', 'spam' => 'eggs']],
        ];
    }

    /**
     * @dataProvider getDecodeDictionaryData
     * @covers ::decodeDictionary
     */
    public function testDecodeDictionary(string $encoded, array $value) : void {
        $this->assertSame($value, $this->decoder->decodeDictionary($encoded));
    }

    /**
     * @covers ::decodeDictionary
     */
    public function testDecodeInvalidDictionary() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter is not an encoded dictionary.');
        $this->decoder->decodeDictionary('4:spam');
    }

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
     */
    public function testGenericDecode(string $encoded, $value) : void {
        $this->assertEquals($value, $this->decoder->decode($encoded));
    }

    /**
     * @covers ::decode
     */
    public function testGenericDecodeWithInvalidData() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter is not correctly encoded.');
        $this->decoder->decode('foo');
    }

    /**
     * @covers ::decodeFile
     * @covers ::decodeFileContents
     */
    public function testDecodeTorrentFileStrictWithMissingAnnounce() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing or empty "announce" key.');
        $this->decoder->decodeFile(__DIR__ . '/_files/testMissingAnnounce.torrent', true);
    }

    /**
     * @covers ::decodeFile
     * @covers ::decodeFileContents
     */
    public function testDecodeTorrentFileStrictWithMissingInfo() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing or empty "info" key.');
        $this->decoder->decodeFile(__DIR__ . '/_files/testMissingInfo.torrent', true);
    }

    /**
     * @covers ::decodeFile
     */
    public function testDecodeNonReadableFile() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^File .*nonExistingFile does not exist or can not be read.$/');
        $this->decoder->decodeFile(__DIR__ . '/nonExistingFile');
    }

    /**
     * @covers ::decodeFile
     * @covers ::decodeFileContents
     */
    public function testDecodeFileWithStrictChecksEnabled() : void {
        $list = $this->decoder->decodeFile(__DIR__ . '/_files/valid.torrent', true);

        $this->assertIsArray($list);
        $this->assertArrayHasKey('announce', $list);
        $this->assertSame('http://trackerurl', $list['announce']);
        $this->assertArrayHasKey('comment', $list);
        $this->assertSame('This is a comment', $list['comment']);
        $this->assertArrayHasKey('creation date', $list);
        $this->assertEquals(1323713688, $list['creation date']);
        $this->assertArrayHasKey('info', $list);
        $this->assertIsArray($list['info']);
        $this->assertArrayHasKey('files', $list['info']);
        $this->assertSame(5, count($list['info']['files']));
        $this->assertArrayHasKey('name', $list['info']);
        $this->assertSame('PHP', $list['info']['name']);
    }
}
