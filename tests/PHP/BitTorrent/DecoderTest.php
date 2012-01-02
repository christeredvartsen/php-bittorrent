<?php
/**
 * PHP_BitTorrent
 *
 * Copyright (c) 2011-2012 Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package PHP_BitTorrent
 * @subpackage UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */

/**
 * @package PHP_BitTorrent
 * @subpackage UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class PHP_BitTorrent_DecoderTest extends PHPUnit_Framework_TestCase {
    public function testDecoderInteger() {
        $encoded = array('i1e', 'i-1e', 'i0e');
        $decoded = array(1, -1, 0);

        for ($i = 0; $i < count($encoded); $i++) {
            $this->assertSame($decoded[$i], PHP_BitTorrent_Decoder::decodeInteger($encoded[$i]));
        }
    }

    public function testDecodeInvalidInteger() {
        $this->setExpectedException('PHP_BitTorrent_Decoder_Exception');
        PHP_BitTorrent_Decoder::decodeInteger('i01e');

        $this->setExpectedException('PHP_BitTorrent_Decoder_Exception');
        PHP_BitTorrent_Decoder::decodeInteger('i-01e');

        $this->setExpectedException('PHP_BitTorrent_Decoder_Exception');
        PHP_BitTorrent_Decoder::decodeInteger('ifoobare');
    }

    public function testDecodeStringAsInteger() {
        $this->setExpectedException('PHP_BitTorrent_Decoder_Exception');
        PHP_BitTorrent_Decoder::decodeInteger('4:spam');
    }

    public function testDecodePartialInteger() {
        $this->setExpectedException('PHP_BitTorrent_Decoder_Exception');
        PHP_BitTorrent_Decoder::decodeInteger('i10');
    }

    public function testDecodeString() {
        $encoded = array('4:spam', '11:test string');
        $decoded = array('spam', 'test string');

        for ($i = 0; $i < count($encoded); $i++) {
            $this->assertSame($decoded[$i], PHP_BitTorrent_Decoder::decodeString($encoded[$i]));
        }
    }

    public function testDecodeInvalidString() {
        $this->setExpectedException('PHP_BitTorrent_Decoder_Exception');
        PHP_BitTorrent_Decoder::decodeString('4spam');
    }

    public function testDecodeStringWithInvalidLength() {
        $this->setExpectedException('PHP_BitTorrent_Decoder_Exception');
        PHP_BitTorrent_Decoder::decodeString('6:spam');
    }

    public function testDecodeStringWithTruncation() {
        $this->assertSame('foo', PHP_BitTorrent_Decoder::decodeString('3:foobar'));
    }

    public function testDecodeList() {
        $encoded = 'li1ei2ei3ee';
        $decoded = array(1, 2, 3);

        $this->assertSame($decoded, PHP_BitTorrent_Decoder::decodeList($encoded));
    }

    public function testDecodeInvalidList() {
        $this->setExpectedException('PHP_BitTorrent_Decoder_Exception');
        PHP_BitTorrent_Decoder::decodeList('4:spam');
    }

    public function testDecodeDictionary() {
        $encoded = 'd3:foo3:bar4:spam4:eggse';
        $decoded = array('foo' => 'bar', 'spam' => 'eggs');

        $this->assertSame($decoded, PHP_BitTorrent_Decoder::decodeDictionary($encoded));
    }

    public function testDecodeInvalidDictionary() {
        $this->setExpectedException('PHP_BitTorrent_Decoder_Exception');
        PHP_BitTorrent_Decoder::decodeDictionary('4:spam');
    }

    public function testGenericDecode() {
        $encoded = array('i1e', '4:spam', 'li1ei2ei3ee', 'd3:foo3:bare');
        $decoded = array(1, 'spam', array(1, 2, 3), array('foo' => 'bar'));

        for ($i = 0; $i < count($encoded); $i++) {
            $this->assertSame($decoded[$i], PHP_BitTorrent_Decoder::decode($encoded[$i]));
        }
    }

    public function testGenericDecodeWithInvalidData() {
        $this->setExpectedException('PHP_BitTorrent_Decoder_Exception');
        PHP_BitTorrent_Decoder::decode('foo');
    }

    public function testDecodeTorrentFileStrictWithMissingAnnounce() {
        $file = __DIR__ . '/_files/testMissingAnnounce.torrent';
        $this->setExpectedException('PHP_BitTorrent_Decoder_Exception');
        PHP_BitTorrent_Decoder::decodeFile($file, true);
    }

    public function testDecodeTorrentFileStrictWithMissingInfo() {
        $file = __DIR__ . '/_files/testMissingInfo.torrent';
        $this->setExpectedException('PHP_BitTorrent_Decoder_Exception');
        PHP_BitTorrent_Decoder::decodeFile($file, true);
    }

    public function testDecodeNonReadableFile() {
        $file = __DIR__ . '/' . uniqid(null, true);
        $this->setExpectedException('PHP_BitTorrent_Decoder_Exception');
        PHP_BitTorrent_Decoder::decodeFile($file);
    }

    public function testDecodeFileWithStrictChecksEnabled() {
        $list = PHP_BitTorrent_Decoder::decodeFile(__DIR__ . '/_files/valid.torrent', true);
    }
}