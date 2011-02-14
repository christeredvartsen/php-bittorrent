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
class PHP_BitTorrent_Tracker_RequestTest extends PHPUnit_Framework_TestCase {
    /**
     * Request object
     *
     * @var PHP_BitTorrent_Tracker_Request
     */
    protected $request = null;

    public function setUp() {
        $this->request = new PHP_BitTorrent_Tracker_Request();
    }

    public function tearDown() {
        $this->request = null;
    }

    public function testSetGetInfoHash() {
        $infoHash = str_pad('20 byte info hash', 20);
        $this->request->info_hash = $infoHash;
        $this->assertSame($infoHash, $this->request->info_hash);
    }

    public function testSetGetPeerId() {
        $peerId = str_pad('20 byte string', 20);
        $this->request->peer_id = $peerId;
        $this->assertSame($peerId, $this->request->peer_id);
    }

    public function testSetGetPort() {
        $port = 80;
        $this->request->port = $port;
        $this->assertSame($port, $this->request->port);
    }

    public function testSetGetDownloaded() {
        $downloaded = 100;
        $this->request->downloaded = $downloaded;
        $this->assertSame($downloaded, $this->request->downloaded);
    }

    public function testSetGetUploaded() {
        $uploaded = 100;
        $this->request->uploaded = $uploaded;
        $this->assertSame($uploaded, $this->request->uploaded);
    }

    public function testSetGetLeft() {
        $left = 100;
        $this->request->left = $left;
        $this->assertSame($left, $this->request->left);
    }

    public function testSetGetIp() {
        $ip = '127.0.0.1';
        $this->request->ip = $ip;
        $this->assertSame($ip, $this->request->ip);
    }

    public function testSetGetUserAgent() {
        $ua = 'UserAgentString';
        $this->request->user_agent = $ua;
        $this->assertSame($ua, $this->request->user_agent);
    }

    public function testSetGetEvent() {
        $event = PHP_BitTorrent_Tracker_Request::EVENT_COMPLETED;
        $this->request->event = $event;
        $this->assertSame($event, $this->request->event);
    }

    public function testIsSeederOrNot() {
        $this->request->left = 0;
        $this->assertTrue($this->request->isSeeder());

        $this->request->left = 1;
        $this->assertFalse($this->request->isSeeder());
    }

    public function testSetGetNonDefaultValue() {
        $key = 'foo';
        $value = 'bar';

        $this->request->$key = $value;
        $this->assertSame($value, $this->request->$key);
    }

    public function testGetNonExistingKey() {
        $this->assertNull($this->request->foobar);
    }

    public function testSetInvalidInfoHash() {
        $infoHash = 'asd';
        $this->setExpectedException('PHP_BitTorrent_Tracker_Request_Exception');
        $this->request->info_hash = $infoHash;

    }

    public function testSetInvalidPeerId() {
        $peerId = 'asd';
        $this->setExpectedException('PHP_BitTorrent_Tracker_Request_Exception');
        $this->request->peer_id = $peerId;

    }

    public function testSetInvalidEvent() {
        $this->setExpectedException('PHP_BitTorrent_Tracker_Request_Exception');
        $this->request->event = 'foobar';
    }

    public function testSetInvalidPort() {
        $this->setExpectedException('PHP_BitTorrent_Tracker_Request_Exception');
        $this->request->port = 0;
    }

    public function testIsSeederWithMissingData() {
        $this->setExpectedException('PHP_BitTorrent_Tracker_Request_Exception');
        $this->request->isSeeder();
    }

    public function testGetClientIpWithHttpXForwardedForPresent() {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.1.1.1';
        $request = new PHP_BitTorrent_Tracker_Request();
        $this->assertSame($_SERVER['HTTP_X_FORWARDED_FOR'], $request->ip);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    public function testGetClientIpWithRemoteAddrPresent() {
        $_SERVER['REMOTE_ADDR'] = '2.2.2.2';
        $request = new PHP_BitTorrent_Tracker_Request();
        $this->assertSame($_SERVER['REMOTE_ADDR'], $request->ip);
        unset($_SERVER['REMOTE_ADDR']);
    }

    public function testValidateOnValidRequest() {
    	$params = array(
            'info_hash'  => str_pad('20 byte info hash', 20),
            'peer_id'    => str_pad('20 byte peer id', 20),
            'ip'         => '1.2.3.4',
            'port'       => '8080',
            'uploaded'   => '123',
            'downloaded' => '321',
            'left'       => '22',
    	);

    	foreach ($params as $key => $val) {
            $this->request->$key = $val;
    	}

        $this->assertTrue($this->request->validate());
    }

    public function testValidateOnInvalidRequest() {
        $params = array(
            'info_hash'  => str_pad('20 byte info hash', 20),
            'peer_id'    => str_pad('20 byte peer id', 20),
        );

        foreach ($params as $key => $val) {
            $this->request->$key = $val;
        }

        $this->setExpectedException('PHP_BitTorrent_Tracker_Request_Exception');
        $this->request->validate();
    }

    public function testSetDataUsingConstructor() {
        $data = array(
            'foo' => 'bar',
            'bar' => 'foo',
        );
        $request = new PHP_BitTorrent_Tracker_Request($data);
        $this->assertSame($data['foo'], $request->foo);
        $this->assertSame($data['bar'], $request->bar);
    }
}