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

/**
 * Abstract storage adapter class
 *
 * @package PHP_BitTorrent
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
abstract class PHP_BitTorrent_Tracker_StorageAdapter_Abstract {
    /**
     * Parameters for the adapter
     *
     * @var array
     */
    protected $params = null;

    /**
     * The current request to the tracker
     *
     * @var PHP_BitTorrent_Tracker_Request
     */
    protected $request = null;

    /**
     * Tracker instance that can be used for logging
     *
     * @var PHP_BitTorrent_Tracker
     */
    protected $tracker = null;

    /**
     * Class constructor
     *
     * @param array $params
     */
    public function __construct(array $params = null) {
        if ($params !== null) {
            // @codeCoverageIgnoreStart
            $this->setParams($params);
        }
        // @codeCoverageIgnoreEnd

        $this->init();
    }

    /**
     * Fetch a single parameter
     *
     * @param string $key
     * @return mixed
     */
    public function getParam($key) {
        return $this->params[$key];
    }

    /**
     * Get the params
     *
     * @return mixed
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Set a single parameter
     *
     * @param string $key
     * @param mixed $value
     */
    public function setParam($key, $value) {
        $this->params[$key] = $value;
    }

    /**
     * Set a set of parameters
     *
     * @param array $params
     */
    public function setParams(array $params) {
        foreach ($params as $key => $value) {
            $this->setParam($key, $value);
        }
    }

    /**
     * Init method
     */
    protected function init() {
        // Can be implemented by child classes
    }

    /**
     * Return the current request
     *
     * @return PHP_BitTorrent_Tracker_Request
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Set the current request
     *
     * @param PHP_BitTorrent_Tracker_Request $request
     * @return PHP_BitTorrent_Tracker_StorageAdapter_Abstract
     */
    public function setRequest(PHP_BitTorrent_Tracker_Request $request) {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the tracker instance (if set)
     *
     * @return PHP_BitTorrent_Tracker
     */
    public function getTracker() {
        return $this->tracker;
    }

    /**
     * Set the tracker instance
     *
     * @param PHP_BitTorrent_Tracker $tracker
     * @return PHP_BitTorrent_Tracker_StorageAdapter_Abstract
     */
    public function setTracker(PHP_BitTorrent_Tracker $tracker) {
        $this->tracker = $tracker;

        return $this;
    }

    /**
     * See if a torrent exist in storage
     *
     * @param string $infoHash
     * @return boolean Return true if the torrent exists or false otherwise
     */
    abstract public function torrentExists($infoHash);

    /**
     * See if a peer exists
     *
     * @param string $infoHash The info hash of the torrent that the peer is sharing.
     * @param string $peerId The id of the peer given by the client.
     * @return boolean Return true if the peer exist or false otherwise
     */
    abstract public function torrentPeerExists($infoHash, $peerId);

    /**
     * Get peers connected to a torrent
     *
     * @param string $infoHash
     * @param boolean $connectable If we only want connectable peers set this to true. If false we will only get peers that can not be connected to
     * @param int $maxGive Max. number of peers to return
     * @param PHP_BitTorrent_Tracker_Peer $excludePeer Peer to exclude from the list
     * @return array An array of PHP_BitTorrent_Tracker_Peer objects
     */
    abstract public function getTorrentPeers($infoHash, $connectable = null, $limit = null, PHP_BitTorrent_Tracker_Peer $excludePeer = null);

    /**
     * Delete a peer connected to a torrent from the database
     *
     * @param string $infoHash The info hash of the torrent
     * @param PHP_BitTorrent_Tracker_Peer $peer The peer to delete
     * @return boolean
     */
    abstract public function deleteTorrentPeer($infoHash, PHP_BitTorrent_Tracker_Peer $peer);

    /**
     * Add a peer to a torrent
     *
     * @param string $infoHash The info hash of the torrent
     * @param PHP_BitTorrent_Tracker_Peer $peer The peer to add
     * @return boolean Returns true if the peer is added or false otherwise
     */
    abstract public function addTorrentPeer($infoHash, PHP_BitTorrent_Tracker_Peer $peer);

    /**
     * Update information about a peer
     *
     * @param string $infoHash The info hash of the torrent
     * @param PHP_BitTorrent_Tracker_Peer $peer The peer making the request
     * @return boolean Returns true if the peer is updated or false otherwise
     */
    abstract public function updateTorrentPeer($infoHash, PHP_BitTorrent_Tracker_Peer $peer);

    /**
     * A peer has finished downloading a torrent
     *
     * @param string $infoHash The info hash of the torrent
     * @param PHP_BitTorrent_Tracker_Peer $peer The peer that completed the torrent
     * @return boolean Returns false on success or false otherwise
     */
    abstract public function torrentComplete($infoHash, PHP_BitTorrent_Tracker_Peer $peer);

    /**
     * Add a torrent to the tracker
     *
     * @param string $infoHash The info hash of the torrent
     * @return boolean Returns true if the torrent was added, false otherwise
     */
    abstract public function addTorrent($infoHash);
}