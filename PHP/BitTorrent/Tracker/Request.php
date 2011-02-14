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
 * Class representing a request made by the tracker
 *
 * @package PHP_BitTorrent
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class PHP_BitTorrent_Tracker_Request {
    /**#@+
     * Event sent from the client
     *
     * @var string
     */
    const EVENT_STARTED   = 'started';
    const EVENT_COMPLETED = 'completed';
    const EVENT_STOPPED   = 'stopped';
    const EVENT_NONE      = '';
    /**#@-*/

    /**#@+
      * Request names that matched the names used in a typical GET request
      *
      * @var string
      */
     const INFO_HASH     = 'info_hash';
     const INFO_HASH_HEX = 'info_hash_hex';
     const PEER_ID       = 'peer_id';
     const PORT          = 'port';
     const DOWNLOADED    = 'downloaded';
     const UPLOADED      = 'uploaded';
     const LEFT          = 'left';
     const IP            = 'ip';
     const USER_AGENT    = 'user_agent';
     const EVENT         = 'event';
     /**#@-*/

    /**
     * The data from the request
     *
     * @var array
     */
    protected $data = array();

    /**
     * The required parameters
     *
     * @var array
     */
    protected $requiredParams = array(
        self::IP,
        self::INFO_HASH,
        self::PEER_ID,
        self::PORT,
        self::UPLOADED,
        self::DOWNLOADED,
        self::LEFT,
    );

    /**
     * Class constructor
     *
     * @param array $data
     */
    public function __construct($data = null) {
        if ($data !== null) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }

        $this->setIp();
    }

    /**
     * Overloading for accessing class property values
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }

    /**
     * Overloading for setting class property values
     *
     * @param string $key
     * @param mixed $value
     * @return PHP_BitTorrent_Tracker_Request
     */
    public function __set($key, $value) {
        switch ($key) {
            case self::EVENT:
                $this->setEvent($value);
                break;
            case self::PORT:
                $this->setPort($value);
                break;
            case self::PEER_ID:
                $this->setPeerId($value);
                break;
            case self::DOWNLOADED:
                $this->setDownloaded($value);
                break;
            case self::UPLOADED:
                $this->setUploaded($value);
                break;
            case self::LEFT:
                $this->setLeft($value);
                break;
            case self::INFO_HASH:
                $this->setInfoHash($value);
                break;
            case self::USER_AGENT:
                $this->setUserAgent($value);
                break;
            case self::IP:
                $this->setClientIp($value);
                break;
            default:
            	$this->data[$key] = $value;
            	break;
        }

        return $this;
    }

    /**
     * Overloading to determine if a property is set
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key) {
        return !empty($this->data[$key]);
    }

    /**
     * See if a request is valid
     *
     * @throws PHP_BitTorrent_Tracker_Request_Exception
     * @return boolean Returns true if the request is valid
     */
    public function validate() {
        foreach ($this->requiredParams as $key) {
            if (!isset($this->data[$key])) {
                throw new PHP_BitTorrent_Tracker_Request_Exception('Missing parameter "' . $key . '"');
            }
        }

        return true;
    }

    /**
     * Set the ip address or host address of the client
     *
     * @param string $ip IP address or hostname
     * @return PHP_BitTorrent_Tracker_Request
     */
    public function setClientIp($ip) {
        $this->data[self::IP] = $ip;

        return $this;
    }

    /**
     * The ip part is optional and if the client does not send it, we will fetch it using the
     * getClientIp method.
     */
    protected function setIp() {
        if (!$this->__isset(self::IP)) {
            $this->__set(self::IP, $this->getClientIp());
        }
    }

    /**
     * Get the ip address of the client
     *
     * @return string
     */
    protected function getClientIp() {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return null;
    }

    /**
     * Set the info hash of the request
     *
     * This method checks the length of the info hash string. If it validates it will create a
     * hexadecimal version of it and store it in the data array.
     *
     * @param string $infoHash
     * @throws PHP_BitTorrent_Tracker_Request_Exception
     * @return PHP_BitTorrent_Tracker_Request
     */
    public function setInfoHash($infoHash) {
        $infoHash = $this->escape($infoHash);

        if (strlen($infoHash) !== 20) {
            throw new PHP_BitTorrent_Tracker_Request_Exception('Invalid info hash: ' . $infoHash);
        }

        $this->data[self::INFO_HASH] = $infoHash;
        $this->data[self::INFO_HASH_HEX] = bin2hex($infoHash);

        return $this;
    }

    /**
     * Set the user agent string
     *
     * @param string $userAgent
     * @return PHP_BitTorrent_Tracker_Request
     */
    public function setUserAgent($userAgent) {
        $this->data[self::USER_AGENT] = $userAgent;

        return $this;
    }

    /**
     * Set the peer id set by the client
     *
     * The peer_id is a 20 byte random string generated by the client.
     *
     * @param string $peerId
     * @throws PHP_BitTorrent_Tracker_Request_Exception
     * @return PHP_BitTorrent_Tracker_Request
     */
    public function setPeerId($peerId) {
        $peerId = $this->escape($peerId);

        if (strlen($peerId) !== 20) {
            throw new PHP_BitTorrent_Tracker_Request_Exception('Invalid peer id: ' . $peerId);
        }

        $this->data[self::PEER_ID] = $peerId;

        return $this;
    }

    /**
     * Set the event
     *
     * @param string $event
     * @throws PHP_BitTorrent_Tracker_Request_Exception
     * @return PHP_BitTorrent_Tracker_Request
     */
    public function setEvent($event) {
        switch ($event) {
            case self::EVENT_NONE:
            case self::EVENT_STARTED:
            case self::EVENT_STOPPED:
            case self::EVENT_COMPLETED:
                $this->data[self::EVENT] = $event;
                break;
            default:
                throw new PHP_BitTorrent_Tracker_Request_Exception('Invalid event: ' . $event);
        }

        return $this;
    }

    /**
     * Set the port
     *
     * @param int $port
     * @throws PHP_BitTorrent_Tracker_Request_Exception
     * @return PHP_BitTorrent_Tracker_Request
     */
    public function setPort($port) {
        $port = (int) $port;

        if (!$port || $port > 65535) {
            throw new PHP_BitTorrent_Tracker_Request_Exception('Invalid port: ' . $port);
        }

        $this->data[self::PORT] = $port;

        return $this;
    }

    /**
     * Set the "downloaded" param
     *
     * @param int $downloaded
     * @return PHP_BitTorrent_Tracker_Request
     */
    public function setDownloaded($downloaded) {
        $this->data[self::DOWNLOADED] = (int) $downloaded;

        return $this;
    }

    /**
     * Set the "uploaded" param
     *
     * @param int $uploaded
     * @return PHP_BitTorrent_Tracker_Request
     */
    public function setUploaded($uploaded) {
        $this->data[self::UPLOADED] = (int) $uploaded;

        return $this;
    }

    /**
     * Set the "left" param
     *
     * @param int $left
     * @return PHP_BitTorrent_Tracker_Request
     */
    public function setLeft($left) {
        $this->data[self::LEFT] = (int) $left;

        return $this;
    }

    /**
     * See if the request is from a seeder ("left" must be 0)
     *
     * @return boolean
     * @throws PHP_BitTorrent_Tracker_Request_Exception
     */
    public function isSeeder() {
        if (!isset($this->data[self::LEFT])) {
            throw new PHP_BitTorrent_Tracker_Request_Exception('Invalid request');
        }

        return !$this->data[self::LEFT];
    }

    /**
     * Escape data from the request
     *
     * @param string $data
     * @return string
     */
    protected function escape($data) {
        return stripslashes($data);
    }
}