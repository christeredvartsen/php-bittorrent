<?php declare(strict_types=1);
namespace BitTorrent;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use InvalidArgumentException;

class Torrent {
    /**
     * Internal encoder
     *
     * @var EncoderInterface
     */
    private $encoder;

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
    private $announceUrl;

    /**
     * The list of announce URLs
     *
     * @var string[]
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
     * @param string $announceUrl Initial announce URL
     * @param EncoderInterface $encoder Internal encoder instance
     */
    public function __construct(string $announceUrl = null, EncoderInterface $encoder = null) {
        if (null !== $announceUrl) {
            $this->announceUrl = $announceUrl;
        }

        if (null === $encoder) {
            $encoder = new Encoder();
        }

        $this->encoder = $encoder;
    }

    /**
     * Set piece length exponent
     *
     * @param int $pieceLengthExp
     * @return self
     */
    public function withPieceLengthExp(int $pieceLengthExp) : self {
        $new = clone $this;
        $new->pieceLengthExp = $pieceLengthExp;

        return $new;
    }

    /**
     * Get the piece length exponent
     *
     * @return int
     */
    public function getPieceLengthExp() : int {
        return $this->pieceLengthExp;
    }

    /**
     * Set the announce URL
     *
     * @param string $announceUrl
     * @return self
     */
    public function withAnnounceUrl(string $announceUrl) : self {
        $new = clone $this;
        $new->announceUrl = $announceUrl;

        return $new;
    }

    /**
     * Get the announce URL
     *
     * @return ?string
     */
    public function getAnnounceUrl() : ?string {
        return $this->announceUrl;
    }

    /**
     * Set the internal encoder
     *
     * @param EncoderInterface $encoder
     * @return self
     */
    public function withEncoder(EncoderInterface $encoder) : self {
        $new = clone $this;
        $new->encoder = $encoder;

        return $new;
    }

    /**
     * Get the encoder
     *
     * @return EncoderInterface
     */
    public function getEncoder() : EncoderInterface {
        return $this->encoder;
    }

    /**
     * Set announce URL list
     *
     * @param string[] $announceList
     * @return self
     */
    public function withAnnounceList(array $announceList) : self {
        $new = clone $this;
        $new->announceList = $announceList;

        return $new;
    }

    /**
     * Get the announce list
     *
     * @return string[]
     */
    public function getAnnounceList() : ?array {
        return $this->announceList;
    }

    /**
     * Set a comment
     *
     * @param string $comment
     * @return self
     */
    public function withComment(string $comment) : self {
        $new = clone $this;
        $new->comment = $comment;

        return $new;
    }

    /**
     * Get the comment
     *
     * @return ?string
     */
    public function getComment() : ?string {
        return $this->comment;
    }

    /**
     * Set the created by field
     *
     * @param string $createdBy
     * @return self
     */
    public function withCreatedBy(string $createdBy) : self {
        $new = clone $this;
        $new->createdBy = $createdBy;

        return $new;
    }

    /**
     * Get the created by string
     *
     * @return ?string
     */
    public function getCreatedBy() : ?string {
        return $this->createdBy;
    }

    /**
     * Set the created at field
     *
     * @param int $createdAt
     * @return self
     */
    public function withCreatedAt(int $createdAt) : self {
        $new = clone $this;
        $new->createdAt = $createdAt;

        return $new;
    }

    /**
     * Get the created at field
     *
     * @return ?int
     */
    public function getCreatedAt() : ?int {
        return $this->createdAt;
    }

    /**
     * Set additional info
     *
     * @param array $info
     * @return self
     */
    public function withInfo(array $info) : self {
        $new = clone $this;
        $new->info = $info;

        return $new;
    }

    /**
     * Get additional info
     *
     * @return ?array
     */
    public function getInfo() : ?array {
        return $this->info;
    }

    /**
     * Set extra metadata
     *
     * @param array $extra
     * @return self
     */
    public function withExtraMeta(array $extra) : self {
        $new = clone $this;
        $new->extraMeta = $extra;

        return $new;
    }

    /**
     * Get extra metadata
     *
     * @return ?array
     */
    public function getExtraMeta() : ?array {
        return $this->extraMeta;
    }

    /**
     * Save the current torrent object to the specified filename
     *
     * This method will save the current object to a file. If the file specified exists it will be
     * overwritten.
     *
     * @param string $path The path to save the file to
     * @throws RuntimeException
     * @return bool Returns true on success or false on failure
     */
    public function save(string $path) : bool {
        if (!is_writable($path) && !is_writable(dirname($path))) {
            throw new InvalidArgumentException(sprintf('Could not open file "%s" for writing.', $path));
        }

        if (null === $announceUrl = $this->getAnnounceUrl()) {
            throw new RuntimeException('Announce URL is missing');
        }

        $torrent = [
            'announce'      => $announceUrl,
            'creation date' => $this->getCreatedAt() ?: time(),
            'info'          => $this->getInfoPart(),
        ];

        if (null !== $announceList = $this->getAnnounceList()) {
            $torrent['announce-list'] = $announceList;
        }

        if (null !== $comment = $this->getComment()) {
            $torrent['comment'] = $comment;
        }

        if (null !== $createdBy = $this->getCreatedBy()) {
            $torrent['created by'] = $createdBy;
        }

        if (null !== ($extra = $this->getExtraMeta()) && is_array($extra)) {
            foreach ($extra as $extraKey => $extraValue) {
                if (array_key_exists($extraKey, $torrent)) {
                    throw new RuntimeException(sprintf('Duplicate key in extra meta info. "%s" already exists.', $extraKey));
                }

                $torrent[$extraKey] = $extraValue;
            }
        }

        return false !== file_put_contents($path, $this->encoder->encodeDictionary($torrent));
    }

    /**
     * Get file list
     *
     * @return array
     */
    public function getFileList() : array {
        $info = $this->getInfoPart();

        if (isset($info['length'])) {
            return [$info['name']];
        }

        return $info['files'];
    }

    /**
     * Get file size
     *
     * @return int
     */
    public function getSize() : int {
        $info = $this->getInfoPart();

        if (isset($info['length'])) {
            return $info['length'];
        }

        return (int) array_sum(array_map(function(array $file) : int {
            return $file['length'];
        }, $this->getFileList()));
    }

    /**
     * Get the name from the info part
     *
     * @return string
     */
    public function getName() : string {
        return $this->getInfoPart()['name'];
    }

    /**
     * Get sha1 hash from the encoded info part
     *
     * @param bool $raw Whether or not to return the hash in raw format
     * @return string
     */
    public function getHash(bool $raw = false) : string {
        return sha1(
            $this->encoder->encodeDictionary($this->getInfoPart()),
            $raw
        );
    }

    /**
     * Get the encoded hash
     *
     * @return string
     */
    public function getEncodedHash() : string {
        return urlencode($this->getHash(true));
    }

    /**
     * Get the info part of the torrent
     *
     * @return array
     */
    private function getInfoPart() : array {
        $info = $this->getInfo();

        if (null === $info) {
            throw new RuntimeException('The info part of the torrent is not set.');
        }

        return $info;
    }

    /**
     * Check if the torrent is private
     *
     * @return bool
     */
    public function isPrivate() : bool {
        $info = $this->getInfoPart();

        return (isset($info['private']) && 1 === $info['private']);
    }

    /**
     * Create torrent instance from a string
     *
     * @param string $contents
     * @param DecoderInterface $decoder
     * @param bool $strict
     * @return self
     */
    static public function createFromString(string $contents, DecoderInterface $decoder, bool $strict = false) : self {
        return static::createFromDictionary($decoder->decodeFileContents($contents, $strict));
    }

    /**
     * Create torrent instance from a torrent file
     *
     * @param string $path
     * @param DecoderInterface $decoder
     * @param bool $strict
     * @return self
     */
    static public function createFromTorrentFile(string $path, DecoderInterface $decoder, bool $strict = false) : self {
        return static::createFromDictionary($decoder->decodeFile($path, $strict));
    }

    /**
     * Create torrent instance from a dictionary
     *
     * @param array $dictionary
     * @return self
     */
    static public function createFromDictionary(array $dictionary) : self {
        $torrent = new self();

        if (isset($dictionary['announce'])) {
            $torrent = $torrent->withAnnounceUrl($dictionary['announce']);
            unset($dictionary['announce']);
        }

        if (isset($dictionary['announce-list'])) {
            $torrent = $torrent->withAnnounceList($dictionary['announce-list']);
            unset($dictionary['announce-list']);
        }

        if (isset($dictionary['comment'])) {
            $torrent = $torrent->withComment($dictionary['comment']);
            unset($dictionary['comment']);
        }

        if (isset($dictionary['created by'])) {
            $torrent = $torrent->withCreatedBy($dictionary['created by']);
            unset($dictionary['created by']);
        }

        if (isset($dictionary['creation date'])) {
            $torrent = $torrent->withCreatedAt($dictionary['creation date']);
            unset($dictionary['creation date']);
        }

        if (isset($dictionary['info'])) {
            $torrent = $torrent->withInfo($dictionary['info']);
            unset($dictionary['info']);
        }

        if (count($dictionary) > 0) {
            $torrent = $torrent->withExtraMeta($dictionary);
        }

        return $torrent;
    }

    /**
     * Create torrent instance from local path
     *
     * @param string $path
     * @param string $announceUrl
     * @throws RuntimeException
     * @return self
     */
    static public function createFromPath(string $path, string $announceUrl = null) : self {
        $torrent = new self($announceUrl);
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
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($absolutePath, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $entry) {
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
            $fp = fopen($p = $filePath . DIRECTORY_SEPARATOR . $files[0]['filename'], 'rb');

            if (false === $fp) {
                throw new RuntimeException(sprintf('Failed to open file: %s', $p));
            }

            while ($position < $info['length']) {
                /** @var string */
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

            usort($files, function(array $a, array $b) : int {
                return strcmp($a['filename'], $b['filename']);
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
                $fp = fopen($p = $absolutePath . DIRECTORY_SEPARATOR . $filename, 'rb');

                if (false === $fp) {
                    throw new RuntimeException(sprintf('Failed to open file: %s', $p));
                }

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

        return $torrent->withInfo($info);
    }
}
