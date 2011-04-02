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
 * @package PHP_BitTorrent
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class PHP_BitTorrent_Tracker_StorageAdapter_Sqlite extends PHP_BitTorrent_Tracker_StorageAdapter_Abstract {
    /**
     * Database resource
     *
     * @var PDO
     */
    protected $db = null;

    /**
     * Fetch the database resource
     *
     * This method will return the current database instance. The first time this method is
     * executed it will create the resource.
     *
     * @throws PHP_BitTorrent_Tracker_StorageAdapter_Sqlite_Exception
     * @return PDO
     */
    protected function getDb() {
        if ($this->db === null) {
            $database = $this->getParam('database');

            // See if we have a database parameter
            if (empty($database)) {
                throw new PHP_BitTorrent_Tracker_StorageAdapter_Sqlite_Exception('Storage adapter missing "database" parameter.');
            }

            try {
                $createTables = false;

                // See if the database exist. If it does, skip the step that creates the tables
                // needed.
                if (!file_exists($database)) {
                    $createTables = true;
                }

                // Get semaphore
                $sem = sem_get(1);

                $this->db = new PDO('sqlite:' . $database);

                // Create tables?
                if ($createTables) {
                    // Acquire semaphore
                    sem_acquire($sem);

                    $sql = "
                        CREATE TABLE peer (
                            torrentId INTEGER NOT NULL default '0',
                            peerId BLOB NOT NULL,
                            ip TEXT NOT NULL,
                            port INTEGER NOT NULL default '0',
                            uploaded INTEGER NOT NULL default '0',
                            downloaded INTEGER NOT NULL default '0',
                            left INTEGER NOT NULL default '0',
                            seeder BOOLEAN NOT NULL default '0',
                            started INTEGER NOT NULL,
                            connectable BOOLEAN NOT NULL default '1',
                            PRIMARY KEY (torrentId,peerId)
                        )
                    ";
                    $this->db->query($sql);

                    $sql = "
                        CREATE TABLE torrent (
                            infoHash BLOB UNIQUE
                        );
                    ";
                    $this->db->query($sql);

                    // Relase semaphore
                    sem_release($sem);
                } else {
                    // Acquire and release semaphore. If another request is currently creating the
                    // tables in the database, this will block until the creation is complete.
                    sem_acquire($sem);
                    sem_release($sem);
                }
            } catch (PDOException $e) {
                throw new PHP_BitTorrent_Tracker_StorageAdapter_Sqlite_Exception('Could not open database: ' . $e->getMessage());
            }
        }

        return $this->db;
    }

    /**
     * See if a torrent exist on the tracker
     *
     * @param string $infoHash
     * @return boolean
     */
    public function torrentExists($infoHash) {
        $sql = "
            SELECT
                _rowid_
            FROM
                torrent
            WHERE
                infoHash = :infoHash
        ";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute(array(':infoHash' => $infoHash));
        $row = $stmt->fetch();

        return !empty($row);
    }

    /**
     * See if a peer exists
     *
     * @param string $infoHash The info hash of the torrent that the peer is sharing.
     * @param string $peerId The id of the peer given by the client.
     * @throws PHP_BitTorrent_Tracker_StorageAdapter_Exception
     * @return boolean
     */
    public function torrentPeerExists($infoHash, $peerId) {
        $sql = "
            SELECT
                p.ip
            FROM
                peer p
            LEFT JOIN
                torrent t
            ON
                p.torrentId = t._rowid_
            WHERE
                p.peerId = :peerId AND
                t.infoHash = :infoHash
        ";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute(array(':peerId' => $peerId, ':infoHash' => $infoHash));
        $row = $stmt->fetch();

        return !empty($row);
    }

    /**
     * Get peers connected to a torrent
     *
     * @param string $infoHash The info hash of the torrent
     * @param boolean $connectable If we only want connectable peers set this to true. If false we
     *                             will only get peers that can not be connected to. Set to null to
     *                             get all peers.
     * @param int $maxGive Max number of peers to return
     * @param PHP_BitTorrent_Tracker_Peer $excludePeer Peer to exclude from the list
     * @return array An array of PHP_BitTorrent_Tracker_Peer objects
     */
    public function getTorrentPeers($infoHash, $connectable = null, $limit = null, PHP_BitTorrent_Tracker_Peer $excludePeer = null) {
        $where = array();
        $where[] = "t.infoHash = :infoHash";

        if ($connectable !== null) {
            $where[] = "p.connectable = :connectable";
        }

        if ($excludePeer !== null) {
            $where[] = "p.peerId != :excludePeerId";
        }

        // Initialize limit clause variable
        $limitClause = null;

        if ($limit !== null) {
            $limitClause = " LIMIT " . (int) $limit;
        }

        $sql = "
            SELECT
                p.ip,
                p.peerId,
                p.port,
                p.downloaded,
                p.uploaded,
                p.left
            FROM
                peer p
            LEFT JOIN
                torrent t
            ON
                p.torrentId = t._rowid_
            WHERE
                " . implode(' AND ', $where) .
            $limitClause;

        $stmt = $this->getDb()->prepare($sql);

        $stmt->bindValue(':infoHash', $infoHash);

        if ($connectable !== null) {
            $stmt->bindValue(':connectable', ($connectable === true ? 1 : 0));
        }

        if ($excludePeer !== null) {
            $stmt->bindValue(':excludePeerId', $excludePeer->getId());
        }

        $stmt->execute();
        $peers = array();

        while ($p = $stmt->fetch()) {
            $peer = new PHP_BitTorrent_Tracker_Peer();
            $peer->setIp($p['ip'])
                 ->setId($p['peerId'])
                 ->setPort($p['port'])
                 ->setDownloaded($p['downloaded'])
                 ->setUploaded($p['uploaded'])
                 ->setLeft($p['left']);

            $peers[] = $peer;
        }

        return $peers;
    }

    /**
     * Delete a peer connected to a torrent from the database
     *
     * @param string $infoHash The info hash of the torrent
     * @param PHP_BitTorrent_Tracker_Peer $peer The peer to delete
     * @return boolean
     */
    public function deleteTorrentPeer($infoHash, PHP_BitTorrent_Tracker_Peer $peer) {
        $torrentId = $this->getTorrentId($infoHash);

        $sql = "
            DELETE FROM
                peer
            WHERE
                torrentId = :torrentId AND
                peerId = :peerId
        ";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute(array(':torrentId' => $torrentId, ':peerId' => $peer->getId()));

        return (bool) $stmt->rowCount();
    }

    /**
     * Add a peer to a torrent
     *
     * @param string $infoHash The info hash of the torrent
     * @param PHP_BitTorrent_Tracker_Peer $peer The peer to add
     * @return boolean Returns true if the peer is added or false otherwise
     */
    public function addTorrentPeer($infoHash, PHP_BitTorrent_Tracker_Peer $peer) {
        $torrentId = $this->getTorrentId($infoHash);

        if (!$torrentId) {
            return false;
        }

        $time = time();

        $sql = "
            INSERT INTO peer (
                torrentId,
                peerId,
                ip,
                port,
                uploaded,
                downloaded,
                left,
                seeder,
                started,
                connectable
            ) VALUES (
                :torrentId,
                :peerId,
                :peerIp,
                :peerPort,
                :peerUploaded,
                :peerDownloaded,
                :peerLeft,
                :peerIsSeed,
                :time,
                :peerIsConnectable
            )
        ";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute(array(
            ':torrentId'         => $torrentId,
            ':peerId'            => $peer->getId(),
            ':peerIp'            => $peer->getIp(),
            ':peerPort'          => $peer->getPort(),
            ':peerUploaded'      => $peer->getUploaded(),
            ':peerDownloaded'    => $peer->getDownloaded(),
            ':peerLeft'          => $peer->getLeft(),
            ':peerIsSeed'        => ($peer->isSeed() ? 1 : 0),
            ':time'              => $time,
            ':peerIsConnectable' => ($peer->isConnectable() ? 1 : 0),
        ));

        return true;
    }

    /**
     * Update information about a peer
     *
     * @param string $infoHash The info hash of the torrent
     * @param PHP_BitTorrent_Tracker_Peer $peer The peer making the request
     * @return boolean Returns true if the peer is updated or false otherwise
     */
    public function updateTorrentPeer($infoHash, PHP_BitTorrent_Tracker_Peer $peer) {
        $torrentId = $this->getTorrentId($infoHash);

        if (!$torrentId) {
            return false;
        }

        // Update information about the peer
        $sql = "
            UPDATE
                peer
            SET
                ip = :peerIp,
                port = :peerPort,
                uploaded = :peerUploaded,
                downloaded = :peerDownloaded,
                left = :peerLeft,
                seeder = :peerIsSeed,
                connectable = :peerIsConnectable
            WHERE
                peerId = :peerId AND
                torrentId = :torrentId
        ";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute(array(
            ':peerIp'            => $peer->getIp(),
            ':peerPort'          => $peer->getPort(),
            ':peerUploaded'      => $peer->getUploaded(),
            ':peerDownloaded'    => $peer->getDownloaded(),
            ':peerLeft'          => $peer->getLeft(),
            ':peerIsSeed'        => ($peer->isSeed() ? 1 : 0),
            ':peerIsConnectable' => ($peer->isConnectable() ? 1 : 0),
            ':peerId'            => $peer->getId(),
            ':torrentId'         => $torrentId,
        ));

        return (bool) $stmt->rowCount();
    }

    /**
     * A peer has finished downloading a torrent
     *
     * @param string $infoHash The info hash of the torrent
     * @param PHP_BitTorrent_Tracker_Peer $peer The peer that completed the torrent
     * @return boolean Returns false on success or false otherwise
     */
    public function torrentComplete($infoHash, PHP_BitTorrent_Tracker_Peer $peer) {
        if ($this->updateTorrentPeer($infoHash, $peer)) {
            $torrentId = $this->getTorrentId($infoHash);

            // Switch peer to seed
            $sql = "
                UPDATE
                    peer
                SET
                    seeder = 1
                WHERE
                    peerId = :peerId AND
                    torrentId = :torrentId
            ";
            $stmt = $this->getDb()->prepare($sql);

            return $stmt->execute(array(
                ':peerId'    => $peer->getId(),
                ':torrentId' => $torrentId)
            );
        }

        return false;
    }

    /**
     * Add a torrent to the tracker
     *
     * @param string $infoHash The info hash of the torrent
     * @return boolean Returns true if the torrent was added, false otherwise
     */
    public function addTorrent($infoHash) {
        $torrentId = $this->getTorrentId($infoHash);

        // If the torrent already exist, return false
        if ($torrentId) {
            return false;
        }

        $sql = "
            INSERT INTO torrent (
                infoHash
            ) VALUES (
                :infoHash
            )
        ";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute(array(':infoHash' => $infoHash));

        return true;
    }

    /**
     * Get the internal ID of a torrent
     *
     * @param string $infoHash The info hash of the torrent
     * @return int The unique ID stored in the database
     */
    protected function getTorrentId($infoHash) {
        $sql = "
            SELECT
                _rowid_
            FROM
                torrent
            WHERE
                infoHash = :infoHash
        ";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute(array(':infoHash' => $infoHash));
        $torrentId = $stmt->fetchColumn();

        $stmt->closeCursor();

        return (int) $torrentId;
    }
}