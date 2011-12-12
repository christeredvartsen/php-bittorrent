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
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */

namespace PHP\BitTorrent;

/**
 * BitTorrent tracker
 *
 * @package PHP_BitTorrent
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class Tracker {
    /**
     * The peer currently talking to the tracker
     *
     * @var \PHP\BitTorrent\Tracker\Peer
     */
    protected $peer = null;

    /**
     * The storage adapter
     *
     * @var PHP\BitTorrent\Tracker\StorageAdapter\AbstractStorage
     */
    protected $storageAdapter = null;

    /**
     * Event listeners attached
     *
     * @var array
     */
    protected $eventListeners = array();

    /**
     * Response to the client
     *
     * @var PHP\BitTorrent\Tracker\Response
     */
    protected $response = null;

    /**
     * The current request from the client
     *
     * @var \PHP\BitTorrent\Tracker\Request
     */
    protected $request = null;

    /**
     * Parameters
     *
     * @var array
     */
    protected $params = array(
        // The interval used by BitTorrent clients to decide on how often to fetch new peers
        'interval' => 3600,

        // Set to false to disable gzip even if the client supports it
        'useCompression' => true,

        // Automatically register all torrents requested
        'autoRegister' => false,

        // Level of compression. 0: No compression, 9: Best compression
        'compressionLevel' => 5,

        // Max. number of peers to give on a request
        'maxGive' => 200,
    );

    /**
     * Class constructor
     *
     * @param array $params Parameters for the tracker
     * @param \PHP\BitTorrent\Tracker\Request $request If not set, the request will be generated
     *                                                based on the content of the $_GET
     *                                                superglobal.
     */
    public function __construct($params = null,
                                \PHP\BitTorrent\Tracker\Request $request = null) {
        if ($params !== null) {
            $this->setParams($params);
        }

        // If the request is null or is not a valid object, create a new object based on the $_GET
        // superglobal
        if ($request === null || !($request instanceof \PHP\BitTorrent\Tracker\Request)) {
            // @codeCoverageIgnoreStart
            $request = new \PHP\BitTorrent\Tracker\Request($_GET);
        }
        // @codeCoverageIgnoreEnd

        // Set the request object
        $this->setRequest($request);
    }

    /**
     * Add a single event listener
     *
     * @param \PHP\BitTorrent\Tracker\EventListener $eventListener
     * @return \PHP\BitTorrent\Tracker
     */
    public function addEventListener(\PHP\BitTorrent\Tracker\EventListener $eventListener) {
        $eventListener->setTracker($this);
        $this->eventListeners[] = $eventListener;

        return $this;
    }

    /**
     * Add several event listeners
     *
     * @param array $eventListeners
     * @return PHP\BitTorrent\Tracker
     */
    public function addEventListeners(array $eventListeners) {
        foreach ($eventListeners as $eventListener) {
            $this->addEventListener($eventListener);
        }

        return $this;
    }

    /**
     * Set the storage adapter
     *
     * @param \PHP\BitTorrent\Tracker\StorageAdapter\AbstractStorage $storageAdapter
     * @return \PHP\BitTorrent\Tracker
     */
    public function setStorageAdapter(\PHP\BitTorrent\Tracker\StorageAdapter\AbstractStorage $storageAdapter) {
        $storageAdapter->setTracker($this);
        $this->storageAdapter = $storageAdapter;

        return $this;
    }

    /**
     * Get the storage adapter
     *
     * @throws \PHP\BitTorrent\Tracker_Exception
     * @return \PHP\BitTorrent\Tracker\StorageAdapter\AbstractStorage
     */
    public function getStorageAdapter() {
        if ($this->storageAdapter === null) {
            throw new \PHP\BitTorrent\Tracker\Exception('No storage adapter set');
        }

        return $this->storageAdapter;
    }

    /**
     * Get the params
     *
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Get a single value from the params
     *
     * @param string $key
     * @return mixed
     */
    public function getParam($key) {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }

        return null;
    }

    /**
     * Set a single value in the params array
     *
     * @param string $key
     * @param mixed $value
     * @return PHP\BitTorrent\Tracker
     */
    public function setParam($key, $value) {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * Set the params array
     *
     * @param array $params
     * @return PHP\BitTorrent\Tracker
     */
    public function setParams(array $params) {
        foreach ($params as $key => $value) {
            $this->setParam($key, $value);
        }

        return $this;
    }

    /**
     * Get the request
     *
     * @return PHP\BitTorrent\Tracker\Request
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Set the request object
     *
     * @param \PHP\BitTorrent\Tracker\Request $request
     * @return \PHP\BitTorrent\Tracker
     */
    public function setRequest(\PHP\BitTorrent\Tracker\Request $request) {
        $this->request = $request;

        return $this;
    }

    /**
     * Trigger an event
     *
     * @param string $event The event to trigger
     */
    public function triggerEvent($event) {
        foreach ($this->eventListeners as $eventListener) {
            $eventListener->$event();
        }
    }

    /**
     * Handle a request
     *
     * This method will handle the current request and return the string that shall be sent to the
     * client.
     *
     * @param boolean $returnResponse Set this to true to return the response as a
     *                                \PHP\BitTorrent\Tracker_Response object instead of printing
     *                                the response to the client. Setting this to true will also
     *                                omit the headers.
     * @return null|\PHP\BitTorrent\Tracker_Response
     * @throws \PHP\BitTorrent\Tracker\Exception
     */
    public function serve($returnResponse = false) {
        $this->triggerEvent('preValidateRequest');

        // Validate the request
        try {
            $this->request->validate();
        } catch (\PHP\BitTorrent\Tracker\Request\Exception $e) {
            throw new \PHP\BitTorrent\Tracker\Exception('Invalid request: ' . $e->getMessage());
        }

        $this->triggerEvent('postValidateRequest');

        $storageAdapter = $this->getStorageAdapter();

        // See if the torrent exists
        if ($storageAdapter->torrentExists($this->request->info_hash) !== true) {
            $this->triggerEvent('torrentDoesNotExist');

            // Do we want to automatically register the torrent?
            if ($this->getParam('autoRegister')) {
                $storageAdapter->addTorrent($this->request->info_hash);
                $this->triggerEvent('torrentAutomaticallyRegistered');
            } else {
                throw new \PHP\BitTorrent\Tracker\Exception('Torrent not found on this tracker');
            }
        }

        // See if the peer exists
        $peerExists = $storageAdapter->torrentPeerExists($this->request->info_hash, $this->request->peer_id);

        // Create a peer object based on the request
        $this->peer = new \PHP\BitTorrent\Tracker\Peer();
        $this->peer->setIp($this->request->ip)
                   ->setId($this->request->peer_id)
                   ->setPort($this->request->port)
                   ->setDownloaded($this->request->downloaded)
                   ->setUploaded($this->request->uploaded)
                   ->setLeft($this->request->left);

        $this->triggerEvent('postCreatedPeer');

        if ($this->request->event === \PHP\BitTorrent\Tracker\Request::EVENT_STOPPED && $peerExists) {
            // If 'stopped' the client has stopped the torrent. If info about the peer exist, delete the peer
            $this->triggerEvent('eventStopped');
            $storageAdapter->deleteTorrentPeer($this->request->info_hash, $this->peer);
        } else if ($this->request->event === \PHP\BitTorrent\Tracker\Request::EVENT_COMPLETED && $peerExists) {
            // If 'completed' the user has downloaded the file
            $this->triggerEvent('eventCompleted');
            $storageAdapter->torrentComplete($this->request->info_hash, $this->peer);
        } else if($this->request->event === \PHP\BitTorrent\Tracker\Request::EVENT_STARTED){
            // If 'started' the client has just started the download. The peer does not exist yet
            $this->triggerEvent('eventStarted');
            $storageAdapter->addTorrentPeer($this->request->info_hash, $this->peer);
        } else {
            if ($peerExists) {
                $this->triggerEvent('eventAnnouncement');
                $storageAdapter->updateTorrentPeer($this->request->info_hash, $this->peer);
            } else {
                throw new \PHP\BitTorrent\Tracker\Exception('Unexpected error');
            }
        }

        // Max number of torrent to give
        $maxGive = (int) $this->getParam('maxGive');

        // Fetch the peers for this torrent (excluding the current one)
        $allPeers = $storageAdapter->getTorrentPeers($this->request->info_hash, null, $maxGive, $this->peer);

        // Force usage of the maxGive param
        $allPeers = array_slice($allPeers, 0, $maxGive);

        $this->triggerEvent('preCreateResponse');

        // Initialize a response
        $response = new \PHP\BitTorrent\Tracker\Response();
        $response->addPeers($allPeers)
                 ->setInterval((int) $this->getParam('interval'));

        // Handle some extra options
        if (!empty($this->request->nopeer_id)) {
            $response->setNoPeerId(true);
        }

        if (!empty($this->request->compact)) {
            $response->setCompact(true);
        }

        $this->response = $response;
        $this->triggerEvent('postCreateResponse');

        // Do we want to return the response instead of sending headers and displaying the
        // response?
        if ($returnResponse) {
            return $this->response;
        }

        // Encode the response
        $responseString = (string) $this->response;

        // Send correct header
        header('Content-Type: text/plain');

        // See if the client supports compression
        if ($this->getParam('useCompression') && isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
                header('Content-Encoding: gzip');
                $responseString = gzencode($responseString, (int) $this->getParam('compressionLevel'), FORCE_GZIP);
            } else if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false) {
                header('Content-Encoding: deflate');
                $responseString = gzencode($responseString, (int) $this->getParam('compressionLevel'), FORCE_DEFLATE);
            }
        }

        // Output response
        print($responseString);
    }

    /**
     * Get the response to the client
     *
     * @return \PHP\BitTorrent\Tracker\Response
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * Get the current peer
     *
     * @return \PHP\BitTorrent\Tracker\Peer
     */
    public function getPeer() {
        return $this->peer;
    }
}