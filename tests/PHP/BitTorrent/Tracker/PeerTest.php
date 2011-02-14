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
class PHP_BitTorrent_Tracker_PeerTest extends PHPUnit_Framework_TestCase {
    /**
     * Peer object
     *
     * @var PHP_BitTorrent_Tracker_Peer
     */
    protected $peer = null;

    /**
     * Set up method that creates a fresh peer objet before each test
     */
    public function setUp() {
        $this->peer = new PHP_BitTorrent_Tracker_Peer();
    }

    /**
     * Set up method that destroys the peer object after each test
     */
    public function tearDown() {
        $this->peer = null;
    }

    public function testSetGetIp() {
        $this->assertNull($this->peer->getIp());
        $ip = '127.0.0.1';
        $this->peer->setIp($ip);
        $this->assertSame($ip, $this->peer->getIp());
    }

    public function testSetGetId() {
        $this->assertNull($this->peer->getId());
        $id = 'Peer id';
        $this->peer->setId($id);
        $this->assertSame($id, $this->peer->getId());
    }

    public function testSetGetPort() {
        $this->assertNull($this->peer->getPort());
        $port = 6666;
        $this->peer->setPort($port);
        $this->assertSame($port, $this->peer->getPort());
    }

    public function testSetGetPortAsString() {
        $port = '6666';
        $this->peer->setPort($port);
        $this->assertSame((int) $port, $this->peer->getPort());
    }

    public function testSetGetDownloaded() {
        $this->assertNull($this->peer->getDownloaded());
        $downloaded = 42;
        $this->peer->setDownloaded($downloaded);
        $this->assertSame($downloaded, $this->peer->getDownloaded());
    }

    public function testSetGetUploaded() {
        $this->assertNull($this->peer->getUploaded());
        $uploaded = 42;
        $this->peer->setUploaded($uploaded);
        $this->assertSame($uploaded, $this->peer->getUploaded());
    }

    public function testSetGetLeft() {
        $this->assertNull($this->peer->getLeft());
        $left = 42;
        $this->peer->setLeft($left);
        $this->assertSame($left, $this->peer->getLeft());
    }

    public function testIsSeed() {
        $this->assertFalse($this->peer->isSeed());
        $this->peer->setLeft(0);
        $this->assertTrue($this->peer->isSeed());
    }
}