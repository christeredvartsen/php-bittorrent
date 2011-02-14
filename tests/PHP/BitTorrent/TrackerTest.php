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
class PHP_BitTorrent_TrackerTest extends PHPUnit_Framework_TestCase {
    /**
     * Tracker instance
     *
     * @var PHP_BitTorrent_Tracker
     */
    protected $tracker = null;

    /**
     * Set up method
     */
    public function setUp() {
        $this->tracker = new PHP_BitTorrent_Tracker();
    }

    /**
     * Tear down method
     */
    public function tearDown() {
        $this->tracker = null;
    }

    /**
     * Try to set and get a single param
     */
    public function testSetandGetParam() {
        $key = 'parameterKey';
        $value = 'parameterValue';

        $this->assertNull($this->tracker->getParam($key));
        $this->tracker->setParam($key, $value);

        $this->assertSame($value, $this->tracker->getParam($key));
    }

    /**
     * Check the default params
     */
    public function testGetDefaultParams() {
        $params = $this->tracker->getParams();

        $this->assertSame($params['interval'], 3600);
        $this->assertSame($params['useCompression'], true);
        $this->assertSame($params['compressionLevel'], 5);
        $this->assertSame($params['maxGive'], 200);
    }

    /**
     * Test the set ang get methods for the params
     */
    public function testSetAndGetParams() {
        $params = array(
            'interval'          => 1800,
            'useCompression'    => false,
            'autoRegister'      => true,
            'compressionLevel'  => 1,
            'maxGive'           => 100,
            'customKey'         => 'customValue',
        );

        $this->tracker->setParams($params);
        $this->assertSame($params, $this->tracker->getParams());
    }

    /**
     * Test the set and get methods for the request
     */
    public function testSetAndGetRequest() {
        $request = new PHP_BitTorrent_Tracker_Request();
        $this->tracker->setRequest($request);

        $this->assertSame($request, $this->tracker->getRequest());
    }

    /**
     * Test the set and get methods for the storage adapter
     */
    public function testSetGetStorageAdapter() {
        $adapter = new PHP_BitTorrent_Tracker_StorageAdapter_Sqlite();
        $this->tracker->setStorageAdapter($adapter);

        $this->assertSame($adapter, $this->tracker->getStorageAdapter());
    }

    /**
     * @expectedException PHP_BitTorrent_Tracker_Exception
     */
    public function testGetStorageAdapterWhenNoneIsSet() {
        $this->tracker->getStorageAdapter();
    }

    /**
     * Test the serve method with an invalid request
     *
     * @expectedException PHP_BitTorrent_Tracker_Exception
     * @expectedExceptionMessage Missing parameter "ip"
     */
    public function testServeWithInvalidRequest() {
        $this->tracker->serve();
    }

    /**
     * Trigger the serve method when torrent is unknown and the auto register feature is not
     * enabled (the default value)
     *
     * @expectedException PHP_BitTorrent_Tracker_Exception
     * @expectedExceptionMessage Torrent not found on this tracker
     */
    public function testServeWithUnknownTorrentAndAutoRegisterIsDisabled() {
        $params = $this->getParamsForRequestMock();
        $request = $this->getMock('PHP_BitTorrent_Tracker_Request', null, array($params));
        $storageAdapter = $this->getMockForAbstractClass('PHP_BitTorrent_Tracker_StorageAdapter_Abstract');

        $this->tracker->setRequest($request)->setStorageAdapter($storageAdapter);
        $this->tracker->serve(true);
    }

    /**
     * Trigger the serve method when torrent is unknown and the auto register feature is enabled
     */
    public function testServeWithUnknownTorrentAndAutoRegisterIsEnabled() {
        $this->tracker->setParam('autoRegister', true);

        $params = $this->getParamsForRequestMock();
        $params['event'] = PHP_BitTorrent_Tracker_Request::EVENT_NONE;

        $request = $this->getMock('PHP_BitTorrent_Tracker_Request', null, array($params));
        $storageAdapter = $this->getMockForAbstractClass('PHP_BitTorrent_Tracker_StorageAdapter_Abstract');
        $storageAdapter->expects($this->once())->method('addTorrent')->with($params['info_hash']);
        $storageAdapter->expects($this->once())->method('torrentPeerExists')->with($params['info_hash'], $params['peer_id'])->will($this->returnValue(true));
        $storageAdapter->expects($this->once())->method('updateTorrentPeer')->with($params['info_hash']);
        $storageAdapter->expects($this->once())->method('getTorrentPeers')->with($params['info_hash'])->will($this->returnValue(array(
            $this->getMock('PHP_BitTorrent_Tracker_Peer'),
        )));

        $eventListener = $this->getMock('PHP_BitTorrent_Tracker_EventListener');
        $eventListener->expects($this->once())->method('torrentAutomaticallyRegistered');

        $this->tracker->setRequest($request)->setStorageAdapter($storageAdapter)->addEventListener($eventListener);
        $response = $this->tracker->serve(true);
        $this->assertInstanceOf('PHP_BitTorrent_Tracker_Response', $response);
    }

    /**
     * Request a started event
     */
    public function testSendStartedEvent() {
        $params = $this->getParamsForRequestMock();
        $params['event'] = PHP_BitTorrent_Tracker_Request::EVENT_STARTED;
        $request = $this->getMock('PHP_BitTorrent_Tracker_Request', null, array($params));

        $storageAdapter = $this->getMockForAbstractClass('PHP_BitTorrent_Tracker_StorageAdapter_Abstract');
        $storageAdapter->expects($this->once())->method('torrentExists')->with($params['info_hash'])->will($this->returnValue(true));
        $storageAdapter->expects($this->once())->method('addTorrentPeer')->with($params['info_hash']);

        $peers = array(
            $this->getMock('PHP_BitTorrent_Tracker_Peer'),
        );

        $storageAdapter->expects($this->once())->method('getTorrentPeers')->with($params['info_hash'])->will($this->returnValue($peers));

        $eventListener = $this->getMock('PHP_BitTorrent_Tracker_EventListener');
        $eventListener->expects($this->once())->method('eventStarted');

        $this->tracker->setRequest($request)
                      ->setStorageAdapter($storageAdapter)
                      ->addEventListener($eventListener);

        $response = $this->tracker->serve(true);

        $this->assertInstanceOf('PHP_BitTorrent_Tracker_Response', $response);
        $this->assertSame($peers, $response->getPeers());
    }

    public function testSendRegularAnnouncementEvent() {
        $params = $this->getParamsForRequestMock();
        $params['event'] = PHP_BitTorrent_Tracker_Request::EVENT_NONE;
        $request = $this->getMock('PHP_BitTorrent_Tracker_Request', null, array($params));

        $storageAdapter = $this->getMockForAbstractClass('PHP_BitTorrent_Tracker_StorageAdapter_Abstract');
        $storageAdapter->expects($this->once())->method('torrentExists')->with($params['info_hash'])->will($this->returnValue(true));
        $storageAdapter->expects($this->once())->method('torrentPeerExists')->with($params['info_hash'])->will($this->returnValue(true));
        $storageAdapter->expects($this->once())->method('updateTorrentPeer')->with($params['info_hash']);
        $storageAdapter->expects($this->once())->method('getTorrentPeers')->with($params['info_hash'])->will($this->returnValue(array(
            $this->getMock('PHP_BitTorrent_Tracker_Peer'),
        )));

        $eventListener = $this->getMock('PHP_BitTorrent_Tracker_EventListener');
        $eventListener->expects($this->once())->method('eventAnnouncement');

        $this->tracker->setRequest($request)
                      ->setStorageAdapter($storageAdapter)
                      ->addEventListener($eventListener);

        $response = $this->tracker->serve(true);

        $this->assertInstanceOf('PHP_BitTorrent_Tracker_Response', $response);
    }

    public function testSendCompletedEvent() {
        $params = $this->getParamsForRequestMock();
        $params['event'] = PHP_BitTorrent_Tracker_Request::EVENT_COMPLETED;
        $request = $this->getMock('PHP_BitTorrent_Tracker_Request', null, array($params));

        $storageAdapter = $this->getMockForAbstractClass('PHP_BitTorrent_Tracker_StorageAdapter_Abstract');
        $storageAdapter->expects($this->once())->method('torrentExists')->with($params['info_hash'])->will($this->returnValue(true));
        $storageAdapter->expects($this->once())->method('torrentPeerExists')->with($params['info_hash'])->will($this->returnValue(true));
        $storageAdapter->expects($this->once())->method('torrentComplete')->with($params['info_hash']);

        $peers = array(
            $this->getMock('PHP_BitTorrent_Tracker_Peer'),
        );

        $storageAdapter->expects($this->once())->method('getTorrentPeers')->with($params['info_hash'])->will($this->returnValue($peers));

        $eventListener = $this->getMock('PHP_BitTorrent_Tracker_EventListener');
        $eventListener->expects($this->once())->method('eventCompleted');

        $this->tracker->setRequest($request)
                      ->setStorageAdapter($storageAdapter)
                      ->addEventListener($eventListener);

        $response = $this->tracker->serve(true);

        $this->assertInstanceOf('PHP_BitTorrent_Tracker_Response', $response);
        $this->assertSame($peers, $response->getPeers());
    }

    public function testSendStopEvent() {
        $params = $this->getParamsForRequestMock();
        $params['event'] = PHP_BitTorrent_Tracker_Request::EVENT_STOPPED;
        $request = $this->getMock('PHP_BitTorrent_Tracker_Request', null, array($params));

        $storageAdapter = $this->getMockForAbstractClass('PHP_BitTorrent_Tracker_StorageAdapter_Abstract');
        $storageAdapter->expects($this->once())->method('torrentExists')->with($params['info_hash'])->will($this->returnValue(true));
        $storageAdapter->expects($this->once())->method('torrentPeerExists')->with($params['info_hash'])->will($this->returnValue(true));
        $storageAdapter->expects($this->once())->method('deleteTorrentPeer')->with($params['info_hash']);

        $peers = array(
            $this->getMock('PHP_BitTorrent_Tracker_Peer'),
        );

        $storageAdapter->expects($this->once())->method('getTorrentPeers')->with($params['info_hash'])->will($this->returnValue($peers));

        $eventListener = $this->getMock('PHP_BitTorrent_Tracker_EventListener');
        $eventListener->expects($this->once())->method('eventStopped');

        $this->tracker->setRequest($request)
                      ->setStorageAdapter($storageAdapter)
                      ->addEventListener($eventListener);

        $response = $this->tracker->serve(true);

        $this->assertInstanceOf('PHP_BitTorrent_Tracker_Response', $response);
        $this->assertSame($peers, $response->getPeers());
    }

    public function testSendInvalidEvent() {
        $params = $this->getParamsForRequestMock();
        $params['event'] = PHP_BitTorrent_Tracker_Request::EVENT_NONE;
        $request = $this->getMock('PHP_BitTorrent_Tracker_Request', null, array($params));

        $storageAdapter = $this->getMockForAbstractClass('PHP_BitTorrent_Tracker_StorageAdapter_Abstract');
        $storageAdapter->expects($this->once())->method('torrentExists')->with($params['info_hash'])->will($this->returnValue(true));
        $storageAdapter->expects($this->once())->method('torrentPeerExists')->with($params['info_hash'])->will($this->returnValue(false));

        $this->tracker->setRequest($request)
                      ->setStorageAdapter($storageAdapter);

        $this->setExpectedException('PHP_BitTorrent_Tracker_Exception', 'Unexpected error');
        $this->tracker->serve(true);
    }

    /**
     * Mock some event listeners, attach them, and trigger some events
     */
    public function testTriggerEvent() {
        // Add a couple of event handlers
        $preValidateRequest = $this->getMock('PHP_BitTorrent_Tracker_EventListener');
        $preValidateRequest->expects($this->once())->method('preValidateRequest');

        $postValidateRequest = $this->getMock('PHP_BitTorrent_Tracker_EventListener');
        $postValidateRequest->expects($this->once())->method('postValidateRequest');

        $prePostValidateRequest = $this->getMock('PHP_BitTorrent_Tracker_EventListener');
        $prePostValidateRequest->expects($this->once())->method('preValidateRequest');
        $prePostValidateRequest->expects($this->once())->method('postValidateRequest');

        $this->tracker->addEventListeners(array($preValidateRequest, $postValidateRequest, $prePostValidateRequest));
        $this->tracker->triggerEvent('preValidateRequest');
        $this->tracker->triggerEvent('postValidateRequest');
    }

    /**
     * Get parameters for a request mock object
     *
     * @return array
     */
    protected function getParamsForRequestMock() {
        return array(
            'info_hash' => str_repeat('a', 20),
            'peer_id' => str_repeat('b', 20),
            'ip' => '127.0.0.1',
            'port' => 22222,
            'uploaded' => 123,
            'downloaded' => 123,
            'left' => 123,
            'event' => PHP_BitTorrent_Tracker_Request::EVENT_NONE,
        );
    }
}