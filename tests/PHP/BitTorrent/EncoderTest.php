<?php
/**
 * PHP BitTorrent
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
 * @subpackage UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */

namespace PHP\BitTorrent;

/**
 * @subpackage UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class EncoderTest extends \PHPUnit_Framework_TestCase {
    /**
     * Encoder instance
     *
     * @var PHP\BitTorrent\Encdoder
     */
    private $encoder;

    /**
     * Set up the encoder
     */
    public function setUp() {
        $this->encoder = new Encoder();
    }

    /**
     * Tear down the encoder
     */
    public function tearDown() {
        $this->encoder = null;
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function getEncodeIntegerData() {
        return array(
            array(-1, 'i-1e'),
            array(0, 'i0e'),
            array(1, 'i1e'),
        );
    }

    /**
     * @dataProvider getEncodeIntegerData()
     */
    public function testEncodeInteger($value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeInteger($value));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEncodeNonIntegerAsInteger() {
        $this->encoder->encodeInteger('1');
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function getEncodeStringData() {
        return array(
            array('spam', '4:spam'),
            array('foobar', '6:foobar'),
            array('foo:bar', '7:foo:bar'),
        );
    }

    /**
     * @dataProvider getEncodeStringData()
     */
    public function testEncodeString($value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeString($value));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEncodeNonStringAsString() {
        $this->encoder->encodeString(1);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function getEncodeListData() {
        return array(
            array(array('spam', 1, array(1)), 'l4:spami1eli1eee'),
        );
    }

    /**
     * @dataProvider getEncodeListData()
     */
    public function testEncodeList($value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeList($value));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEncodeNonListAsList() {
        $this->encoder->encodeList(1);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function getEncodeDictionaryData() {
        return array(
            array(array('1' => 'foo', 'foo' => 'bar', 'list' => array(1, 2, 3)), 'd1:13:foo3:foo3:bar4:listli1ei2ei3eee'),
        );
    }

    /**
     * @dataProvider getEncodeDictionaryData()
     */
    public function testEncodeDictionary($value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeDictionary($value));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEncodeDictionaryListAsDictionary() {
        $this->encoder->encodeDictionary('foo');
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function getEncodeData() {
        return array(
            array(1, 'i1e'),
            array('spam', '4:spam'),
            array(array(1, 2), 'li1ei2ee'),
            array(array('foo' => 'bar', 'spam' => 'sucks'), 'd3:foo3:bar4:spam5:suckse'),
        );
    }

    /**
     * @dataProvider getEncodeData()
     */
    public function testEncodeUsingGenericMethod($value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encode($value));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEncodeNonSupportedType() {
        $this->encoder->encode(new \stdClass());
    }
}
