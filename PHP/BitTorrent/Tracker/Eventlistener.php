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
 * Base event listener class
 *
 * All event listeners used with the tracker must extend this base class
 *
 * @package PHP_BitTorrent
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @codeCoverageIgnore
 */
class PHP_BitTorrent_Tracker_EventListener {
    /**
     * Instance of the tracker
     *
     * Each event listener will have an instance of the tracker that can be used to fetch data
     * stored in the tracker like for instance the current peer or the current request.
     *
     * @var PHP_BitTorrent_Tracker
     */
    protected $tracker = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Init method that can be implemented by event listeners
     */
    protected function init() {

    }

    /**
     * Set the tracker instance
     *
     * @param PHP_BitTorrent_Tracker $tracker
     * @return PHP_BitTorrent_Tracker_EventListener_Abstract
     */
    public function setTracker(PHP_BitTorrent_Tracker $tracker) {
        $this->tracker = $tracker;

        return $this;
    }

    /**
     * Pre validate request event
     *
     * This event is triggered before the request is validated in the tracker.
     */
    public function preValidateRequest() {

    }

    /**
     * Post validate request event
     *
     * This event is triggered after the request has been validated.
     */
    public function postValidateRequest() {

    }

    /**
     * Torrent does not exist on the tracker
     *
     * This event is triggered if the torrent could not be found on the tracker.
     */
    public function torrentDoesNotExist() {

    }

    /**
     * Post created peer event
     *
     * This event is triggered after the current peer object is created based on the request.
     */
    public function postCreatedPeer() {

    }

    /**
     * This event is triggered when a client sends a "stop" event
     */
    public function eventStopped() {

    }

    /**
     * This event is triggered when a client sends a "completed" event
     */
    public function eventCompleted() {

    }

    /**
     * This event is triggered when a client sends a "started" event
     */
    public function eventStarted() {

    }

    /**
     * This event is triggered when a client sends a regular announcement
     */
    public function eventAnnouncement() {

    }

    /**
     * This event is triggered before the response object is created
     */
    public function preCreateResponse() {

    }

    /**
     * This event is triggered after the response object has been created
     */
    public function postCreateResponse() {

    }

    /**
     * This event is triggered whenever a torrent is automatically registered
     */
    public function torrentAutomaticallyRegistered() {

    }
}