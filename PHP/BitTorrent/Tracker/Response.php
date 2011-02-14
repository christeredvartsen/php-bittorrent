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
 * Class representing a response from the tracker
 *
 * @package PHP_BitTorrent
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class PHP_BitTorrent_Tracker_Response {
    /**
     * Interval in the response
     *
     * @var int
     */
    protected $interval = 3600;

    /**
     * Peers in the response
     *
     * @var array Array of PHP_BitTorrent_Tracker_Response objects
     */
    protected $peers = array();

    /**
     * Wether or not to include the peer id in the response
     *
     * @var boolean
     */
    protected $noPeerId = false;

    /**
     * Wether or not to generate a compact response
     *
     * @var boolean
     */
    protected $compact = false;

    /**
     * Set the interval in the response
     *
     * @param int $interval
     * @return PHP_BitTorrent_Tracker_Response
     */
    public function setInterval($interval) {
        $this->interval = (int) $interval;

        return $this;
    }

    /**
     * Set the noPeerId flag
     *
     * @param boolean $flag
     * @return PHP_BitTorrent_Tracker_Response
     */
    public function setNoPeerId($flag) {
        $this->noPeerId = (bool) $flag;

        return $this;
    }

    /**
     * Get the noPeerId flag
     *
     * @return boolean
     */
    public function getNoPeerId() {
        return $this->noPeerId;
    }

    /**
     * Set the compatct flag
     *
     * @param boolean $flag
     * @return PHP_BitTorrent_Tracker_Response
     */
    public function setCompact($flag) {
        $this->compact = (bool) $flag;

        return $this;
    }

    /**
     * Get the compact flag
     *
     * @return boolean
     */
    public function getCompact() {
        return $this->compact;
    }

    /**
     * Get the interval in the response
     *
     * @return int
     */
    public function getInterval() {
        return $this->interval;
    }

    /**
     * Add a peer to the response
     *
     * @param PHP_BitTorrent_Tracker_Peer $peer
     * @return PHP_BitTorrent_Tracker_Response
     */
    public function addPeer(PHP_BitTorrent_Tracker_Peer $peer) {
        $this->peers[] = $peer;

        return $this;
    }

    /**
     * Add peers
     *
     * @param array $peers Array of PHP_BitTorrent_Tracker_Peer objects
     * @return PHP_BitTorrent_Tracker_Response
     */
    public function addPeers($peers = array()) {
        foreach ($peers as $peer) {
            $this->addPeer($peer);
        }

        return $this;
    }

    /**
     * Get all peers
     *
     * @return array Array of PHP_BitTorrent_Tracker_Peer objects
     */
    public function getPeers() {
        return $this->peers;
    }

    /**
     * Magic to string method
     *
     * This method generates a string of the current response object that can be sent to a
     * BitTorrent client (BitTorrent encoded dictionary).
     *
     * @return string
     */
    public function __toString() {
        // Initialize (in)complete variables
        $complete = 0;
        $incomplete = 0;

        if ($this->compact) {
            // Compact response
            $peers = '';

            foreach ($this->peers as $peer) {
                $peers .= pack('Nn', ip2long($peer->getIp()), $peer->getPort());

                if ($peer->isSeed()) {
                    $complete++;
                } else {
                    $incomplete++;
                }
            }
        } else {
            // Regular response
            $peers = array();

            foreach ($this->peers as $peer) {
                $p = array(
                    'ip'   => $peer->getIp(),
                    'port' => $peer->getPort(),
                );

                // Include peer id unless specified otherwise
                if (!$this->noPeerId) {
                    $p['peer id'] = $peer->getId();
                }

                $peers[] = $p;

                if ($peer->isSeed()) {
                    $complete++;
                } else {
                    $incomplete++;
                }
            }
        }

        $response = array(
            'interval'   => $this->getInterval(),
            'complete'   => $complete,
            'incomplete' => $incomplete,
            'peers'      => $peers,
        );

        // Return the encoded the response
        return PHP_BitTorrent_Encoder::encodeDictionary($response);
    }
}