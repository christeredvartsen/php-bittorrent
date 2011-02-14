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
class PHP_BitTorrent_Tracker_ResponseTest extends PHPUnit_Framework_TestCase {
    /**
     * Response object
     *
     * @var PHP_BitTorrent_Tracker_Response
     */
    protected $response = null;

    public function setUp() {
        $this->response = new PHP_BitTorrent_Tracker_Response();
    }

    public function tearDown() {
        $this->response= null;
    }

    public function testDefaultValues() {
        $this->assertSame(3600, $this->response->getInterval());
        $this->assertSame(array(), $this->response->getPeers());
    }

    public function testSetGetInterval() {
        $interval = 60;

        $this->response->setInterval($interval);
        $this->assertSame($interval, $this->response->getInterval());
    }

    public function testAddAndGetSinglePeer() {
        $peer = new PHP_BitTorrent_Tracker_Peer();

        $this->response->addPeer($peer);
        $peers = $this->response->getPeers();

        $this->assertSame(1, count($peers));
        $this->assertSame($peer, $peers[0]);
    }

    public function testAddAndGetSeveralPeers() {
        $peers = array(
            new PHP_BitTorrent_Tracker_Peer(),
            new PHP_BitTorrent_Tracker_Peer(),
            new PHP_BitTorrent_Tracker_Peer(),
        );

        $this->response->addPeers($peers);
        $this->assertSame($peers, $this->response->getPeers());
    }

    public function testSetGetNoPeerId() {
        $this->response->setNoPeerId(true);
        $this->assertTrue($this->response->getNoPeerId());
        $this->response->setNoPeerId(false);
        $this->assertFalse($this->response->getNoPeerId());
    }

    public function testSetGetCompact() {
        $this->response->setCompact(true);
        $this->assertTrue($this->response->getCompact());
        $this->response->setCompact(false);
        $this->assertFalse($this->response->getCompact());
    }

    public function testMagicToStringMethod() {
        // Add a peer
        $leech = new PHP_BitTorrent_Tracker_Peer();
        $leech->setId('id#1')->setIp('127.0.0.1')->setPort(123)->setLeft(123);

        $seed = new PHP_BitTorrent_Tracker_Peer();
        $seed->setId('id#2')->setIp('127.0.0.2')->setPort(1234)->setLeft(0);

        $this->response->addPeer($leech)->addPeer($seed);
        $response = (string) $this->response;

        // Decode the response
        $responseDecoded = PHP_BitTorrent_Decoder::decode($response);

        $this->assertSame(1, $responseDecoded['complete']);
        $this->assertSame(1, $responseDecoded['incomplete']);
        $this->assertSame(2, count($responseDecoded['peers']));
    }

    public function testMagicToStringMethodWhenCompactModeIsEnabled() {
        $this->response->setCompact(true);

        // Add a peer
        $leech = new PHP_BitTorrent_Tracker_Peer();
        $leech->setId('id#1')->setIp('127.0.0.1')->setPort(123)->setLeft(123);

        $seed = new PHP_BitTorrent_Tracker_Peer();
        $seed->setId('id#2')->setIp('127.0.0.2')->setPort(1234)->setLeft(0);

        $this->response->addPeer($leech)->addPeer($seed);
        $response = (string) $this->response;

        // Decode the response
        $responseDecoded = PHP_BitTorrent_Decoder::decode($response);
        $this->assertSame(1, $responseDecoded['complete']);
        $this->assertSame(1, $responseDecoded['incomplete']);
        $this->assertInternalType('string', $responseDecoded['peers']);
    }
}