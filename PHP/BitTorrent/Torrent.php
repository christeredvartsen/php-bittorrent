<?php
/**
 * PHP BitTorrent
 *
 * Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
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
 * @package Torrent
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/php-bittorrent
 */

namespace PHP\BitTorrent;

use RecursiveDirectoryIterator,
    RecursiveIteratorIterator,
    RuntimeException,
    InvalidArgumentException;

/**
 * A class that represents a single torrent file
 *
 * This class represents a torrent file. It also has methods for loading from a torrent file, and
 * loading from a directory or a single file.
 *
 * @package Torrent
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/php-bittorrent
 */
class Torrent {
    /**
     * The exponent to use when making the pieces
     *
     * @var int
     */
    private $pieceLengthExp = 18;

    /**
     * The announce URL
     *
     * @var string
     */
    private $announce;

    /**
     * Optional comment
     *
     * @var string
     */
    private $comment;

    /**
     * Optional string that informs clients who or what created this torrent
     *
     * @var string
     */
    private $createdBy = 'PHP BitTorrent';

    /**
     * The unix timestamp of when the torrent was created
     *
     * @var int
     */
    private $createdAt;

    /**
     * Info about the file(s) in the torrent
     *
     * @var array
     */
    private $info;

    /**
     * Class constructor
     *
     * @param string $announceUrl Optional announce URL
     */
    public function __construct($announceUrl = null) {
        if ($announceUrl !== null) {
            $this->setAnnounce($announceUrl);
        }
    }

    /**
     * Populate the instance of the object based on a torrent file
     *
     * @param string $path Path to the torrent file
     * @param PHP\BitTorrent\Decoder $decoder The decoder to use to decode the file
     * @throws InvalidArgumentException
     * @return PHP\BitTorrent\Torrent Returns a new instance of this class
     */
    static public function createFromTorrentFile($path, Decoder $decoder = null) {
        if (!is_file($path)) {
            throw new InvalidArgumentException($path . ' does not exist.');
        }

        // Make sure we have a decoder
        if ($decoder === null) {
            $decoder = new Decoder();
        }

        $decodedFile = $decoder->decodeFile($path);

        // Create a new torrent
        $torrent = new static();

        // Populate the object with data from the file
        if (isset($decodedFile['announce'])) {
            $torrent->setAnnounce($decodedFile['announce']);
        }

        if (isset($decodedFile['comment'])) {
            $torrent->setComment($decodedFile['comment']);
        }

        if (isset($decodedFile['created by'])) {
            $torrent->setCreatedBy($decodedFile['created by']);
        }

        if (isset($decodedFile['creation date'])) {
            $torrent->setCreatedAt($decodedFile['creation date']);
        }

        if (isset($decodedFile['info'])) {
            $torrent->setInfo($decodedFile['info']);
        }

        return $torrent;
    }

    /**
     * Build a torrent from a path
     *
     * Some of the code in this method is ported directly from the official btmakemetafile script
     * by Bram Cohen.
     *
     * @param string $path Path to a directory or a single file
     * @param string $announceUrl URL to the announce
     * @return PHP\BitTorrent\Torrent Returns a new instance of this class
     */
    static public function createFromPath($path, $announceUrl) {
        // Create a new torrent instance
        $torrent = new static($announceUrl);

        // Initialize array of the files to include in the torrent
        $files = array();

        // Generate an absolute path
        $absolutePath = realpath($path);
        $pathIsFile = false;

        // See if we have a single file
        if (is_file($absolutePath)) {
            $pathIsFile = true;
            $files[] = array(
                'filename' => basename($absolutePath),
                'filesize' => filesize($absolutePath),
            );
        } else if (is_dir($absolutePath)) {
            $dir = new RecursiveDirectoryIterator($absolutePath);
            $iterator = new RecursiveIteratorIterator($dir);

            foreach ($iterator as $entry) {
                $filename = $entry->getFilename();

                if ($filename === '.' || $filename === '..') {
                    continue;
                }

                $files[] = array(
                    'filename' => str_replace($absolutePath . DIRECTORY_SEPARATOR, '', (string) $entry),
                    'filesize' => $entry->getSize(),
                );
            }
        } else {
            throw new InvalidArgumentException('Invalid path: ' . $path);
        }

        // Initialize the info part of the torrent
        $info = array(
            'piece length' => pow(2, $torrent->getPieceLengthExp())
        );

        // Initialize the pieces
        $pieces = array();

        // If we only have a single file, get the size of the file and set the name property
        if ($pathIsFile) {
            // Regenerate the path to the file
            $filePath = dirname($absolutePath);

            // The name of the file in the torrent
            $info['name'] = $files[0]['filename'];

            // The size of the file
            $info['length'] = $files[0]['filesize'];

            // The current position in the file
            $position = 0;

            // Open the file
            $fp = fopen($filePath . DIRECTORY_SEPARATOR . $files[0]['filename'], 'rb');

            while ($position < $info['length']) {
                $part = fread($fp, min($info['piece length'], $info['length'] - $position));
                $pieces[] = sha1($part, true);

                $position += $info['piece length'];

                if ($position > $info['length']) {
                    $position = $info['length'];
                }
            }

            // Close the file handle
            fclose($fp);

            $pieces = implode('', $pieces);
        } else {
            // The name of the directory in the torrent
            $info['name'] = basename($absolutePath);

            // Sort the filelist to mimic btmakemetafile
            usort($files, function($a, $b) {
                if ($a['filename'] < $b['filename']) {
                    return -1;
                } else if ($a['filename'] > $b['filename']) {
                    return 1;
                }

                return 0;
            });

            // Initialize some helper variables for the hashing or the parts of each file
            $part = '';
            $done = 0;

            // Loop through all the files in the $files array to generate the pieces and the other
            // stuff in the info part of the torrent. Note that two files may be part of the same
            // piece since btmakemetafile uses cyclic buffers
            foreach ($files as $file) {
                $filename = $file['filename'];
                $filesize = $file['filesize'];

                $info['files'][] = array(
                    'length' => $filesize,
                    'path'   => explode(DIRECTORY_SEPARATOR, $filename)
                );

                // Reset the position in the current file
                $position = 0;

                // Open the current file
                $fp = fopen($absolutePath . DIRECTORY_SEPARATOR . $filename, 'rb');

                // Loop through the file
                while ($position < $filesize) {
                    $bytes = min(($filesize - $position), ($info['piece length'] - $done));
                    $part .= fread($fp, $bytes);

                    $done += $bytes;
                    $position += $bytes;

                    // We have a piece. Add it to the array and reset the helper variables
                    if ($done === $info['piece length']) {
                        $pieces[] = sha1($part, true);
                        $part = '';
                        $done = 0;
                    }
                }

                // Close the file handle
                fclose($fp);
            }

            // If there is a part still not hashed then add it to the pieces array
            if ($done > 0) {
                $pieces[] = sha1($part, true);
            }

            // Make a string of the pieces
            $pieces = implode('', $pieces);
        }

        // Store the pieces in the $info array
        $info['pieces'] = $pieces;

        // Sort the info array
        ksort($info);

        // Set the info
        $torrent->setInfo($info);

        return $torrent;
    }

    /**
     * Set the piece length exponent
     *
     * @param int $pieceLengthExp The exponent to set
     * @return PHP\BitTorrent\Torrent Returns self for a fluent interface
     */
    public function setPieceLengthExp($pieceLengthExp) {
        $this->pieceLengthExp = (int) $pieceLengthExp;

        return $this;
    }

    /**
     * Get the piece length exponent
     *
     * @return int Returns the piece length exponent used when creating a torrent instance from a
     *             path
     */
    public function getPieceLengthExp() {
        return $this->pieceLengthExp;
    }

    /**
     * Set the announce URL
     *
     * @param string $announceUrl The URL to set
     * @return PHP\BitTorrent\Torrent Returns self for a fluent interface
     */
    public function setAnnounce($announceUrl) {
        $this->announce = $announceUrl;

        return $this;
    }

    /**
     * Get the announce URL
     *
     * @return string Returns the URL to the tracker (if set)
     */
    public function getAnnounce() {
        return $this->announce;
    }

    /**
     * Set the comment
     *
     * @param string $comment Comment to attach to the torrent file
     * @return PHP\BitTorrent\Torrent Returns self for a fluent interface
     */
    public function setComment($comment) {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get the comment
     *
     * @return string Returns an optional comment
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Set the created by property
     *
     * @param string $createdBy Who/what created the torrent file
     * @return PHP\BitTorrent\Torrent Returns self for a fluent interface
     */
    public function setCreatedBy($createdBy) {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by property
     *
     * @return string Returns who created the torrent (if set)
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * Set the creation timestamp
     *
     * @param int $createdAt Unix timestamp
     * @return PHP\BitTorrent\Torrent Returns self for a fluent interface
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = (int) $createdAt;

        return $this;
    }

    /**
     * Get the creation timestamp
     *
     * @return int Returns a unix timestamp
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set the info part of the torrent
     *
     * @param array $info Array with information about the torrent file
     * @return PHP\BitTorrent\Torrent Returns self for a fluent interface
     */
    public function setInfo(array $info) {
        $this->info = $info;

        return $this;
    }

    /**
     * Get the info part
     *
     * @return array Returns the info part of the torrent
     */
    public function getInfo() {
        return $this->info;
    }

    /**
     * Save the current torrent object to the specified filename
     *
     * This method will save the current object to a file. If the file specified exists it will be
     * overwritten.
     *
     * @param string $filename Path to the torrent file we want to save
     * @param PHP\BitTorrent\Encoder $encoder Encoder used to encode the information
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @return PHP\BitTorrent\Torrent Returns self for a fluent interface
     */
    public function save($filename, Encoder $encoder = null) {
        if (!is_writable($filename) && !is_writable(dirname($filename))) {
            throw new InvalidArgumentException('Could not open file "' . $filename . '" for writing.');
        }

        $announce = $this->getAnnounce();

        if (empty($announce)) {
            throw new RuntimeException('Announce URL is missing.');
        }

        $info = $this->getInfo();

        if (empty($info)) {
            throw new RuntimeException('The info part of the torrent is empty.');
        }

        if ($encoder === null) {
            $encoder = new Encoder();
        }

        $createdAt = $this->getCreatedAt();

        if (empty($createdAt)) {
            $createdAt = time();
        }

        $torrent = array(
            'announce'      => $announce,
            'creation date' => $createdAt,
            'info'          => $info,
        );

        if (($comment = $this->getComment()) !== null) {
            $torrent['comment'] = $comment;
        }

        if (($createdBy = $this->getCreatedBy()) !== null) {
            $torrent['created by'] = $createdBy;
        }

        // Create the encoded dictionary
        $dictionary = $encoder->encodeDictionary($torrent);

        // Write the encoded data to the file
        file_put_contents($filename, $dictionary);

        return $this;
    }

    /**
     * Get the files listed in the torrent
     *
     * If the torrent is a multifile torrent, return the files array. If it contains a single file,
     * return the name element from the info array.
     *
     * @return string|array Returns a string if the torrent only contains one file or an array of
     *                      files otherwise.
     * @throws RuntimeException
     */
    public function getFileList() {
        $info = $this->getInfo();

        if ($info === null) {
            throw new RuntimeException('The info part of the torrent is not set.');
        }

        if (isset($info['length'])) {
            return $info['name'];
        }

        return $info['files'];
    }

    /**
     * Get the size of the files in the torrent
     *
     * @return int Returns the size of the files in the torrent in bytes
     * @throws RuntimeException
     */
    public function getSize() {
    	$info = $this->getInfo();

        if ($info === null) {
            throw new RuntimeException('The info part of the torrent is not set.');
        }

        // If the length element is set, return that one. If not, loop through the files and generate the total
        if (isset($info['length'])) {
            return $info['length'];
        }

        $files = $this->getFileList();
        $size  = 0;

        foreach ($files as $file) {
            $size += $file['length'];
        }

        return $size;
    }

    /**
     * Get the name that the content will be saved as
     *
     * @return string The name of the torrent
     * @throws RuntimeException
     */
    public function getName() {
    	$info = $this->getInfo();

        if ($info === null) {
            throw new RuntimeException('The info part of the torrent is not set.');
        }

        return $info['name'];
    }
}
