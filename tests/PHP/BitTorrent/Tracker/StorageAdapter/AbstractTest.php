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
class PHP_BitTorrent_Tracker_StorageAdapter_AbstractTest extends PHPUnit_Framework_TestCase {
    /**
     * @var PHP_BitTorrent_Tracker_StorageAdapter_Abstract
     */
    protected $adapter = null;

    public function setUp() {
        $this->adapter = $this->getMockForAbstractClass('PHP_BitTorrent_Tracker_StorageAdapter_Abstract');
    }

    public function tearDown() {
        $this->adapter = null;
    }

    public function testSetGetParams() {
        $params = array(
            'foo' => 'bar',
            'bar' => 'foo',
        );
        $this->adapter->setParams($params);
        $this->assertSame($params, $this->adapter->getParams());
    }

    public function testSetGetParam() {
        $key = 'foo';
        $value = 'bar';
        $this->adapter->setParam($key, $value);
        $this->assertSame($value, $this->adapter->getParam($key));
    }

    public function testSetGetRequest() {
        $request = $this->getMock('PHP_BitTorrent_Tracker_Request');
        $this->adapter->setRequest($request);
        $this->assertSame($request, $this->adapter->getRequest());
    }

    public function testSetGetTracker() {
        $tracker = $this->getMock('PHP_BitTorrent_Tracker');
        $this->adapter->setTracker($tracker);
        $this->assertSame($tracker, $this->adapter->getTracker());
    }
}