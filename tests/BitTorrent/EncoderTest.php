<?php
/**
 * This file is part of the PHP BitTorrent package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace PHP\BitTorrent;

/**
 * @package UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @covers PHP\BitTorrent\Encoder
 */
class EncoderTest extends \PHPUnit_Framework_TestCase {
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
     * Tear down the encoder
     */
    public function tearDown() {
        $this->encoder = null;
    }

    /**
     * Data provider
     *
     * @return array[]
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
     * @covers PHP\BitTorrent\Encoder::encodeInteger
     * @covers PHP\BitTorrent\Encoder::isInt
     */
    public function testEncodeInteger($value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeInteger($value));
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers PHP\BitTorrent\Encoder::encodeInteger
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
        return array(
            array('spam', '4:spam'),
            array('foobar', '6:foobar'),
            array('foo:bar', '7:foo:bar'),
        );
    }

    /**
     * @dataProvider getEncodeStringData()
     * @covers PHP\BitTorrent\Encoder::encodeString
     */
    public function testEncodeString($value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeString($value));
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers PHP\BitTorrent\Encoder::encodeString
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
        return array(
            array(array('spam', 1, array(1)), 'l4:spami1eli1eee'),
        );
    }

    /**
     * @dataProvider getEncodeListData()
     * @covers PHP\BitTorrent\Encoder::encodeList
     */
    public function testEncodeList($value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeList($value));
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers PHP\BitTorrent\Encoder::encodeList
     */
    public function testEncodeNonListAsList() {
        $this->encoder->encodeList(1);
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getEncodeDictionaryData() {
        return array(
            array(array('1' => 'foo', 'foo' => 'bar', 'list' => array(1, 2, 3)), 'd3:foo3:bar4:listli1ei2ei3ee1:13:fooe'),
            array(array('foo' => 'bar', 'spam' => 'eggs'), 'd3:foo3:bar4:spam4:eggse'),
            array(array('spam' => 'eggs', 'foo' => 'bar'), 'd3:foo3:bar4:spam4:eggse'),
        );
    }

    /**
     * @dataProvider getEncodeDictionaryData()
     * @covers PHP\BitTorrent\Encoder::encodeDictionary
     */
    public function testEncodeDictionary($value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encodeDictionary($value));
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers PHP\BitTorrent\Encoder::encodeDictionary
     */
    public function testEncodeDictionaryListAsDictionary() {
        $this->encoder->encodeDictionary('foo');
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function getEncodeData() {
        return array(
            array(1, 'i1e'),
            array('spam', '4:spam'),
            array(array(1, 2, 3), 'li1ei2ei3ee'),
            array(array('foo' => 'bar', 'spam' => 'sucks'), 'd3:foo3:bar4:spam5:suckse'),
        );
    }

    /**
     * @dataProvider getEncodeData()
     * @covers PHP\BitTorrent\Encoder::encode
     * @covers PHP\BitTorrent\Encoder::isInt
     */
    public function testEncodeUsingGenericMethod($value, $encoded) {
        $this->assertSame($encoded, $this->encoder->encode($value));
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers PHP\BitTorrent\Encoder::encode
     */
    public function testEncodeNonSupportedType() {
        $this->encoder->encode(new \stdClass());
    }

    /**
     * @covers PHP\BitTorrent\Encoder::encode
     * @covers PHP\BitTorrent\Encoder::setParam
     */
    public function testCanEncodeEmptyArraysAsDictionaries() {
        $this->assertSame('le', $this->encoder->encode(array()));
        $this->assertSame($this->encoder, $this->encoder->setParam('encodeEmptyArrayAsDictionary', true));
        $this->assertSame('de', $this->encoder->encode(array()));
        $this->assertSame($this->encoder, $this->encoder->setParam('encodeEmptyArrayAsDictionary', false));
        $this->assertSame('le', $this->encoder->encode(array()));
    }
}
