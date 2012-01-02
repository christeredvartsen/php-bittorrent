<?php
/**
 * PHP_BitTorrent
 *
 * Copyright (c) 2011-2012 Christer Edvartsen <cogo@starzinger.net>
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
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */

/**
 * A class that represents a single torrent file
 *
 * This class represents a torrent file. It also has methods for loading from a torrent file, and loading from a directory or a single file
 *
 * @package PHP_BitTorrent
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class PHP_BitTorrent_Torrent {
    /**
     * The exponent to use when making the pieces
     *
     * @var int
     */
    protected $pieceLengthExp = 18;

    /**
     * The announce url
     *
     * @var string
     */
    protected $announce = null;

    /**
     * Optional comment
     *
     * @var string
     */
    protected $comment = null;

    /**
     * Optional string that informs clients who or what created this torrent
     *
     * @var string
     */
    protected $createdBy = 'PHP_BitTorrent';

    /**
     * The unix timestamp of when the torrent was created
     *
     * @var int
     */
    protected $createdAt = null;

    /**
     * Info about the file(s) in the torrent
     *
     * @var array
     */
    protected $info = null;

    /**
     * Reset information in the object
     *
     * This method is called when someone triggers {@see loadFromTorrentFile} or
     * {@see loadFromPath} effectively resetting all properties.
     */
    protected function reset() {
        $this->announce = null;
        $this->commend  = null;
        $this->created  = null;
        $this->info     = null;
    }

    /**
     * Populate the instance of the object based on a torrent file
     *
     * @param string $pathToFile Path to the torrent file
     */
    public function loadFromTorrentFile($path) {
        $this->reset();

        $decodedFile = PHP_BitTorrent_Decoder::decodeFile($path);

        // Populate the object with data from the file
        if (isset($decodedFile['announce'])) {
            $this->setAnnounce($decodedFile['announce']);
        }

        if (isset($decodedFile['comment'])) {
            $this->setComment($decodedFile['comment']);
        }

        if (isset($decodedFile['created by'])) {
            $this->setCreatedBy($decodedFile['created by']);
        }

        if (isset($decodedFile['creation date'])) {
            $this->setCreatedAt($decodedFile['creation date']);
        }

        if (isset($decodedFile['info'])) {
            $this->setInfo($decodedFile['info']);
        }
    }

    /**
     * Build a torrent from a path
     *
     * Some of the code in this method is ported directly from the official btmakemetafile script
     * by Bram Cohen.
     *
     * @param string $path Path to a directory or a single file
     * @param string $announceUrl URL to the announce
     * @return PHP_BitTorrent_Torrent
     */
    public function loadFromPath($path) {
        $this->reset();

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
                'filesize' => filesize($absolutePath)
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
                    'filesize' => $entry->getSize()
                );
            }
        }

        // Initialize the info part of the torrent
        $info = array(
            'piece length' => pow(2, $this->getPieceLengthExp())
        );

        // If we only have a single file, get the size of the file and set the name property
        if ($pathIsFile) {
            // Regenerate the path to the file
            $filePath = dirname($absolutePath);

            // The name of the file in the torrent
            $info['name'] = $files[0]['filename'];

            // The size of the file
            $info['length'] = $files[0]['filesize'];

            // Initialize the pieces
            $pieces = array();

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
            // Initialize the pieces array
            $pieces = array();

            // The name of the directory in the torrent
            $info['name'] = basename($absolutePath);

            // Sort the filelist to mimic btmakemetafile
            usort($files, function($a, $b) {
                if ($a['filename'] < $b['filename']) {
                    return -1;
                } else if ($a['filename'] > $b['filename']) {
                    return 1;
                }
                // @codeCoverageIgnoreStart
                return 0;
            });
            // @codeCoverageIgnoreEnd

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
                        // @codeCoverageIgnoreStart
                        $pieces[] = sha1($part, true);
                        $part = '';
                        $done = 0;
                    }
                    // @codeCoverageIgnoreEnd
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
        $this->setInfo($info);

        return $this;
    }

    /**
     * Set the piece length exponent
     *
     * @param int $pieceLengthExp
     * @return PHP_BitTorrent_Torrent
     */
    public function setPieceLengthExp($pieceLengthExp) {
        $this->pieceLengthExp = (int) $pieceLengthExp;

        return $this;
    }

    /**
     * Get the piece length exponent
     *
     * @return int
     */
    public function getPieceLengthExp() {
        return $this->pieceLengthExp;
    }

    /**
     * Set the announce url
     *
     * @param string $announceUrl
     * @return PHP_BitTorrent_Torrent
     */
    public function setAnnounce($announceUrl) {
        $this->announce = $announceUrl;

        return $this;
    }

    /**
     * Get the announce url
     *
     * @return mixed Returns null if the announce url is not set or a string otherwise
     */
    public function getAnnounce() {
        return $this->announce;
    }

    /**
     * Set the comment
     *
     * @param string $comment
     * @return PHP_BitTorrent_Torrent
     */
    public function setComment($comment) {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get the comment
     *
     * @return mixed Returns null if the comment is not set or a string otherwise
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Set the created by property
     *
     * @param string $createdBy
     * @return PHP_BitTorrent_Torrent
     */
    public function setCreatedBy($createdBy) {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by property
     *
     * @return string
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * Set the creation timestamp
     *
     * @param int $createdAt
     * @return PHP_BitTorrent_Torrent
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
     * @param array $info
     * @return PHP_BitTorrent_Torrent
     */
    public function setInfo(array $info) {
        $this->info = $info;

        return $this;
    }

    /**
     * Get the info part
     *
     * @return array
     */
    public function getInfo() {
        return $this->info;
    }

    /**
     * Save the current torrent object to the filename specified
     *
     * This method will save the current object to a file. If the file specified exists it will be overwritten.
     *
     * @param string $filename
     * @throws PHP_BitTorrent_Torrent_Exception
     * @return PHP_BitTorrent_Torrent
     */
    public function save($filename) {
        if (!is_writable($filename) && !is_writable(dirname($filename))) {
            throw new PHP_BitTorrent_Torrent_Exception('Could not open file "' . $filename . '" for writing.');
        }

        $announce = $this->getAnnounce();

        if (empty($announce)) {
            throw new PHP_BitTorrent_Torrent_Exception('Announce URL is missing.');
        }

        $info = $this->getInfo();

        if (empty($info)) {
            throw new PHP_BitTorrent_Torrent_Exception('The info part of the torrent is empty.');
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
        $dictionary = PHP_BitTorrent_Encoder::encodeDictionary($torrent);

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
     * @return mixed Returns a string if the torrent only contains one file or an array of files otherwise.
     * @throws PHP_BitTorrent_Torrent_Exception
     */
    public function getFileList() {
        $info = $this->getInfo();

        if ($info === null) {
            throw new PHP_BitTorrent_Torrent_Exception('The info part of the torrent is not set.');
        }

        if (isset($info['length'])) {
            return $info['name'];
        }

        return $info['files'];
    }

    /**
     * Get the size of the files in the torrent
     *
     * @return int
     * @throws PHP_BitTorrent_Torrent_Exception
     */
    public function getSize() {
    	$info = $this->getInfo();

        if ($info === null) {
            throw new PHP_BitTorrent_Torrent_Exception('The info part of the torrent is not set.');
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
     * @return string
     * @throws PHP_BitTorrent_Torrent_Exception
     */
    public function getName() {
    	$info = $this->getInfo();

        if ($info === null) {
            throw new PHP_BitTorrent_Torrent_Exception('The info part of the torrent is not set.');
        }

        return $info['name'];
    }
}