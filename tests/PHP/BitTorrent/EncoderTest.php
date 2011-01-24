<?php
/**
 * PHP_BitTorrent
 *
 * Copyright (c) 2011 Christer Edvartsen <cogo@starzinger.net>
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
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */

/**
 * @package PHP_BitTorrent
 * @subpackage UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class PHP_BitTorrent_EncoderTest extends PHPUnit_Framework_TestCase {
    public function testEncodeInteger() {
        $decoded = array(-1, 0, 1);
        $encoded = array('i-1e', 'i0e', 'i1e');

        for ($i = 0; $i < count($decoded); $i++) {
            $this->assertSame($encoded[$i], PHP_BitTorrent_Encoder::encodeInteger($decoded[$i]));
        }
    }

    public function testEncodeNonIntegerAsInteger() {
        $this->setExpectedException('PHP_BitTorrent_Encoder_Exception');
        PHP_BitTorrent_Encoder::encodeInteger('1');
    }

    public function testEncodeString() {
        $decoded = array('spam', 'foobar', 'foo:bar');
        $encoded = array('4:spam', '6:foobar', '7:foo:bar');

        for ($i = 0; $i < count($decoded); $i++) {
            $this->assertSame($encoded[$i], PHP_BitTorrent_Encoder::encodeString($decoded[$i]));
        }
    }

    public function testEncodeNonStringAsString() {
        $this->setExpectedException('PHP_BitTorrent_Encoder_Exception');
        PHP_BitTorrent_Encoder::encodeString(1);
    }

    public function testEncodeList() {
        $decoded = array('spam', 1, array(1));
        $encoded = 'l4:spami1eli1eee';

        $this->assertSame($encoded, PHP_BitTorrent_Encoder::encodeList($decoded));
    }

    public function testEncodeNonListAsList() {
        $this->setExpectedException('PHP_BitTorrent_Encoder_Exception');
        PHP_BitTorrent_Encoder::encodeList(1);
    }

    public function testEncodeDictionary() {
        $decoded = array('1' => 'foo', 'foo' => 'bar', 'list' => array(1, 2, 3));
        $encoded = 'd1:13:foo3:foo3:bar4:listli1ei2ei3eee';

        $this->assertSame($encoded, PHP_BitTorrent_Encoder::encodeDictionary($decoded));
    }

    public function testEncodeDictionaryListAsDictionary() {
        $this->setExpectedException('PHP_BitTorrent_Encoder_Exception');
        PHP_BitTorrent_Encoder::encodeDictionary('foo');
    }

    public function testEncodeUsingGenericMethod() {
        $decoded = array(1, 'spam', array(1, 2), array('foo' => 'bar', 'spam' => 'sucks'));
        $encoded = array('i1e', '4:spam', 'li1ei2ee', 'd3:foo3:bar4:spam5:suckse');

        for ($i = 0; $i < count($decoded); $i++) {
            $this->assertSame($encoded[$i], PHP_BitTorrent_Encoder::encode($decoded[$i]));
        }
    }

    public function testEncodeNonSupportedType() {
        $this->setExpectedException('PHP_BitTorrent_Encoder_Exception');
        PHP_BitTorrent_Encoder::encode(new stdClass());
    }
}