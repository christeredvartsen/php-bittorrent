<?php declare(strict_types=1);
namespace BitTorrent;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use InvalidArgumentException;

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
     * The list of announce URLs
     *
     * @var array
     */
    private $announceList = [];

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
     * Any non-standard fields in the torrent meta data
     *
     * @var array
     */
    private $extraMeta;

    /**
     * Class constructor
     *
     * @param string $announceUrl Optional announce URL
     */
    public function __construct(string $announceUrl = null) {
        if ($announceUrl !== null) {
            $this->setAnnounce($announceUrl);
        }
    }

    /**
     * Populate the instance of the object based on a torrent file
     *
     * @param string $path Path to the torrent file
     * @param DecoderInterface $decoder The decoder to use to decode the file
     * @throws InvalidArgumentException
     * @return Torrent Returns a new instance of this class
     */
    static public function createFromTorrentFile($path, DecoderInterface $decoder = null) {
        if (!is_file($path)) {
            throw new InvalidArgumentException(sprintf('%s does not exist.', $path));
        }

        if ($decoder === null) {
            $decoder = new Decoder();
        }

        $decodedFile = $decoder->decodeFile($path);
        $torrent = new static();

        if (isset($decodedFile['announce'])) {
            $torrent->setAnnounce($decodedFile['announce']);
            unset($decodedFile['announce']);
        }

        if (isset($decodedFile['announce-list'])) {
            $torrent->setAnnounceList($decodedFile['announce-list']);
            unset($decodedFile['announce-list']);
        }

        if (isset($decodedFile['comment'])) {
            $torrent->setComment($decodedFile['comment']);
            unset($decodedFile['comment']);
        }

        if (isset($decodedFile['created by'])) {
            $torrent->setCreatedBy($decodedFile['created by']);
            unset($decodedFile['created by']);
        }

        if (isset($decodedFile['creation date'])) {
            $torrent->setCreatedAt($decodedFile['creation date']);
            unset($decodedFile['creation date']);
        }

        if (isset($decodedFile['info'])) {
            $torrent->setInfo($decodedFile['info']);
            unset($decodedFile['info']);
        }

        if (count($decodedFile) > 0) {
            $torrent->setExtraMeta($decodedFile);
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
     * @return Torrent Returns a new instance of this class
     */
    static public function createFromPath($path, $announceUrl) {
        $torrent = new static($announceUrl);
        $files = [];

        $absolutePath = realpath($path);
        $pathIsFile = false;

        if (false !== $absolutePath && is_file($absolutePath)) {
            $pathIsFile = true;
            $files[] = [
                'filename' => basename($absolutePath),
                'filesize' => filesize($absolutePath),
            ];
        } else if (false !== $absolutePath && is_dir($absolutePath)) {
            $dir = new RecursiveDirectoryIterator($absolutePath);
            $iterator = new RecursiveIteratorIterator($dir);

            foreach ($iterator as $entry) {
                $filename = $entry->getFilename();

                if ($filename === '.' || $filename === '..') {
                    continue;
                }

                $files[] = [
                    'filename' => str_replace($absolutePath . DIRECTORY_SEPARATOR, '', (string) $entry),
                    'filesize' => $entry->getSize(),
                ];
            }
        } else {
            throw new InvalidArgumentException(sprintf('Invalid path: %s', $path));
        }

        $info = [
            'piece length' => pow(2, $torrent->getPieceLengthExp())
        ];

        $pieces = [];

        if ($pathIsFile) {
            $filePath = dirname($absolutePath);
            $info['name'] = $files[0]['filename'];
            $info['length'] = $files[0]['filesize'];
            $position = 0;
            $fp = fopen($filePath . DIRECTORY_SEPARATOR . $files[0]['filename'], 'rb');

            while ($position < $info['length']) {
                $part = fread($fp, min($info['piece length'], $info['length'] - $position));
                $pieces[] = sha1($part, true);

                $position += $info['piece length'];

                if ($position > $info['length']) {
                    $position = $info['length'];
                }
            }

            fclose($fp);

            $pieces = implode('', $pieces);
        } else {
            $info['name'] = basename($absolutePath);

            usort($files, function($a, $b) {
                if ($a['filename'] < $b['filename']) {
                    return -1;
                } else if ($a['filename'] > $b['filename']) {
                    return 1;
                }

                return 0;
            });

            $part = '';
            $done = 0;

            // Loop through all the files in the $files array to generate the pieces and the other
            // stuff in the info part of the torrent. Note that two files may be part of the same
            // piece since btmakemetafile uses cyclic buffers
            foreach ($files as $file) {
                $filename = $file['filename'];
                $filesize = $file['filesize'];

                $info['files'][] = [
                    'length' => $filesize,
                    'path'   => explode(DIRECTORY_SEPARATOR, $filename)
                ];

                $position = 0;
                $fp = fopen($absolutePath . DIRECTORY_SEPARATOR . $filename, 'rb');

                while ($position < $filesize) {
                    $bytes = min(($filesize - $position), ($info['piece length'] - $done));
                    $part .= fread($fp, $bytes);

                    $done += $bytes;
                    $position += $bytes;

                    if ($done === $info['piece length']) {
                        $pieces[] = sha1($part, true);
                        $part = '';
                        $done = 0;
                    }
                }

                fclose($fp);
            }

            if ($done > 0) {
                $pieces[] = sha1($part, true);
            }

            $pieces = implode('', $pieces);
        }

        $info['pieces'] = $pieces;
        ksort($info);
        $torrent->setInfo($info);

        return $torrent;
    }

    /**
     * Set the piece length exponent
     *
     * @param int $pieceLengthExp The exponent to set
     * @return self Returns self for a fluent interface
     */
    public function setPieceLengthExp(int $pieceLengthExp) : self {
        $this->pieceLengthExp = $pieceLengthExp;

        return $this;
    }

    /**
     * Get the piece length exponent
     *
     * @return int Returns the piece length exponent used when creating a torrent instance from a
     *             path
     */
    public function getPieceLengthExp() : int {
        return $this->pieceLengthExp;
    }

    /**
     * Set the announce URL
     *
     * @param string $announceUrl The URL to set
     * @return self Returns self for a fluent interface
     */
    public function setAnnounce(string $announceUrl) : self {
        $this->announce = $announceUrl;

        return $this;
    }

    /**
     * Get the announce URL
     *
     * @return string Returns the URL to the tracker (if set)
     */
    public function getAnnounce() : ?string {
        return $this->announce;
    }

    /**
     * Set the announce list
     *
     * @param array $announceList The array of URLs to set
     * @return self Returns self for a fluent interface
     */
    public function setAnnounceList(array $announceList) : self {
        $this->announceList = $announceList;

        return $this;
    }

    /**
     * Get the announce list
     *
     * @return array Returns the URL to the tracker (if set)
     */
    public function getAnnounceList() : ?array {
        return $this->announceList;
    }

    /**
     * Set the comment
     *
     * @param string $comment Comment to attach to the torrent file
     * @return Torrent Returns self for a fluent interface
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
     * @return Torrent Returns self for a fluent interface
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
     * @return Torrent Returns self for a fluent interface
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
     * @return Torrent Returns self for a fluent interface
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
     * Set an array of non-standard meta info that will be encoded in this torrent
     *
     * @param array $extra Array with information about the torrent file
     * @return Torrent Returns self for a fluent interface
     */
    public function setExtraMeta(array $extra) {
        $this->extraMeta = $extra;

        return $this;
    }

    /**
     * Get extra meta info data
     *
     * @return array Returns an array of any non-standard meta info on this torrent
     */
    public function getExtraMeta() {
        return $this->extraMeta;
    }

    /**
     * Save the current torrent object to the specified filename
     *
     * This method will save the current object to a file. If the file specified exists it will be
     * overwritten.
     *
     * @param string $filename Path to the torrent file we want to save
     * @param EncoderInterface $encoder Encoder used to encode the information
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @return Torrent Returns self for a fluent interface
     */
    public function save($filename, EncoderInterface $encoder = null) {
        if (!is_writable($filename) && !is_writable(dirname($filename))) {
            throw new InvalidArgumentException(sprintf('Could not open file "%s" for writing.', $filename));
        }

        $info = $this->getInfoPart();

        if ($encoder === null) {
            $encoder = new Encoder();
        }

        $createdAt = $this->getCreatedAt();

        if (empty($createdAt)) {
            $createdAt = time();
        }

        $torrent = [
            'creation date' => $createdAt,
            'info'          => $info,
        ];

        if (($announce = $this->getAnnounce()) !== null) {
            $torrent['announce'] = $announce;
        }

        if (($announceList = $this->getAnnounceList()) !== null) {
            $torrent['announce-list'] = $announceList;
        }

        if (($comment = $this->getComment()) !== null) {
            $torrent['comment'] = $comment;
        }

        if (($createdBy = $this->getCreatedBy()) !== null) {
            $torrent['created by'] = $createdBy;
        }

        if (($extra = $this->getExtraMeta()) !== null && is_array($extra)) {
            foreach ($extra as $extraKey => $extraValue) {
                if (array_key_exists($extraKey, $torrent)) {
                    throw new RuntimeException(sprintf('Duplicate key in extra meta info. "%s" already exists.', $extraKey));
                }
                $torrent[$extraKey] = $extraValue;
            }
        }

        file_put_contents($filename, $encoder->encodeDictionary($torrent));

        return $this;
    }

    /**
     * Get the files listed in the torrent
     *
     * @return string[] List of files
     */
    public function getFileList() : array {
        $info = $this->getInfoPart();

        if (isset($info['length'])) {
            return [$info['name']];
        }

        return $info['files'];
    }

    /**
     * Get the size of the files in the torrent
     *
     * @return int Returns the size of the files in the torrent in bytes
     */
    public function getSize() : int {
        $info = $this->getInfoPart();

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
     */
    public function getName() : string {
        return $this->getInfoPart()['name'];
    }

    /**
     * Get the hash of the torrent file
     *
     * @param bool $raw Set to true to return the raw 20-byte hash
     * @return string The torrent hash
     */
    public function getHash(bool $raw = false) : string {
        return sha1(
            (new Encoder())->encodeDictionary($this->getInfoPart()),
            $raw
        );
    }

    /**
     * Get the urlencoded raw hash of the torrent file
     *
     * @return string The torrent hash
     */
    public function getEncodedHash() : string {
        return urlencode($this->getHash(true));
    }

    /**
     * Get the info part of torrent and throw exception if not set
     *
     * @return array
     * @throws RuntimeException
     */
    private function getInfoPart() : array {
        $info = $this->getInfo();

        if (null === $info) {
            throw new RuntimeException('The info part of the torrent is not set.');
        }

        return $info;
    }

    /**
     * Check if the torrent is private or not (via the optional private flag)
     *
     * @return bool
     */
    public function isPrivate() : bool {
        $info = $this->getInfoPart();

        return (isset($info['private']) && 1 === $info['private']) ? true : false;
    }
}
