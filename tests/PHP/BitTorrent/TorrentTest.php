<?php
/**
 * PHP BitTorrent
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
 * @subpackage UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */

namespace PHP\BitTorrent;

/**
 * @subpackage UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class TorrentTest extends \PHPUnit_Framework_TestCase {
    /**
     * Torrent object
     *
     * @var PHP\BitTorrent\Torrent
     */
    private $torrent;

    public function setUp() {
        $this->torrent = new Torrent();
    }

    public function tearDown() {
        $this->torrent = null;
    }

    public function testSetGetComment() {
        $comment = 'This is my comment';
        $this->assertSame($this->torrent, $this->torrent->setComment($comment));
        $this->assertSame($comment, $this->torrent->getComment());
    }

    public function testSetGetCreatedBy() {
        $createdBy = 'Some client name';
        $this->assertSame($this->torrent, $this->torrent->setCreatedBy($createdBy));
        $this->assertSame($createdBy, $this->torrent->getCreatedBy());
    }

    public function testSetGetCreationDate() {
        $timestamp = time();
        $this->assertSame($this->torrent, $this->torrent->setCreatedAt($timestamp));
        $this->assertSame($timestamp, $this->torrent->getCreatedAt());
    }

    public function testSetGetInfo() {
        $info = array('some' => 'data');
        $this->assertSame($this->torrent, $this->torrent->setInfo($info));
        $this->assertSame($info, $this->torrent->getInfo());
    }

    public function testSetGetAnnounce() {
        $announce = 'http://tracker/';
        $this->assertSame($this->torrent, $this->torrent->setAnnounce($announce));
        $this->assertSame($announce, $this->torrent->getAnnounce());
    }

    public function testSetGetPieceLengthExp() {
        $exp = 6;
        $this->assertSame($this->torrent, $this->torrent->setPieceLengthExp($exp));
        $this->assertSame($exp, $this->torrent->getPieceLengthExp());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetNameWithNoInfoBlockAdded() {
        $this->torrent->getName();
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetSizeWithNoInfoBlockAdded() {
        $this->torrent->getSize();
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetFileListWithNoInfoBlockAdded() {
        $this->torrent->getFileList();
    }

    public function testGetName() {
        $name = 'Some name';
        $info = array('name' => $name);
        $this->torrent->setInfo($info);
        $this->assertSame($name, $this->torrent->getName());
    }

    public function testGetSizeWhenLengthIsPresentInTheInfoBlock() {
        $length = 123;
        $info = array('length' => $length);
        $this->torrent->setInfo($info);
        $this->assertSame($length, $this->torrent->getSize());
    }

    public function testGetFileListWhenInfoBlockOnlyContainsOneFile() {
        $name = 'some_filename';
        $info = array('length' => 123, 'name' => $name);
        $this->torrent->setInfo($info);
        $this->assertSame($name, $this->torrent->getFileList());
    }

    public function testGetFileList() {
        $files = array(
            array('length' => 12, 'path' => array('path', 'file.php')),
            array('length' => 32, 'path' => array('path2', 'file2.php')),
            array('length' => 123, 'path' => array('file.php')),
        );
        $info = array('files' => $files);
        $this->torrent->setInfo($info);
        $this->assertSame($files, $this->torrent->getFileList());
    }

    public function testGetSizeWhenInfoBlockHasSeveralFiles() {
        $files = array(
            array('length' =>  12, 'path' => array('path', 'file.php')),
            array('length' =>  32, 'path' => array('path2', 'file2.php')),
            array('length' => 123, 'path' => array('file.php')),
        );
        $info = array('files' => $files);
        $this->torrent->setInfo($info);
        $this->assertSame(167, $this->torrent->getSize());
    }

    public function testCreateFromTorrentFile() {
        $torrent = Torrent::createFromTorrentFile(__DIR__ . '/_files/valid.torrent');

        $this->assertSame('http://trackerurl', $torrent->getAnnounce());
        $this->assertSame('This is a comment', $torrent->getComment());
        $this->assertSame('PHP BitTorrent', $torrent->getCreatedBy());
        $this->assertSame(1323713688, $torrent->getCreatedAt());
        $this->assertSame(30243, $torrent->getSize());
        $this->assertSame(5, count($torrent->getFileList()));
    }

    public function testCreateFromPathWhenUsingADirectoryAsArgument() {
        $path = __DIR__ . '/_files';
        $trackerUrl = 'http://trackerurl';
        $torrent = Torrent::createFromPath($path, $trackerUrl);

        $this->assertSame($trackerUrl, $torrent->getAnnounce());
        $this->assertSame('_files', $torrent->getName());
        $this->assertSame(482, $torrent->getSize());
        $this->assertSame(3, count($torrent->getFileList()));
    }

    public function testCreateFromPathWhenUsingAFileAsArgument() {
        $path = __DIR__ . '/_files/valid.torrent';
        $trackerUrl = 'http://trackerurl';
        $torrent = Torrent::createFromPath($path, $trackerUrl);

        $this->assertSame($trackerUrl, $torrent->getAnnounce());
        $this->assertSame('valid.torrent', $torrent->getName());
        $this->assertSame(440, $torrent->getSize());
        $this->assertSame(1, count($torrent->getFileList()));
    }

    public function testSaveTorrent() {
        $path      = __DIR__ . '/_files';
        $announce  = 'http://tracker/';
        $comment   = 'Some comment';
        $createdBy = 'PHPUnit';
        $target    = tempnam(sys_get_temp_dir(), 'PHP\BitTorrent');

        if (!$target) {
            $this->fail('Could not create file: ' . $target);
        }

        $torrent = Torrent::createFromPath($path, $announce);
        $torrent->setComment($comment)
                ->setCreatedBy($createdBy)
                ->save($target);

        // Now load the file and make sure the values are correct
        $torrent = Torrent::createFromTorrentFile($target);

        $this->assertSame($announce, $torrent->getAnnounce());
        $this->assertSame($comment, $torrent->getComment());
        $this->assertSame($createdBy, $torrent->getCreatedBy());
        $this->assertSame('_files', $torrent->getName());
        $this->assertSame(482, $torrent->getSize());
        $this->assertSame(3, count($torrent->getFileList()));

        // Remove the saved file
        unlink($target);
    }

    /**
     * Try to save when no announce has been given. The code we are testing is AFTER the code that
     * checks if the file specified is writeable, so make sure the argument to save() is a file that
     * is writeable.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Announce URL is missing
     */
    public function testSaveWithNoAnnounce() {
        $target = tempnam(sys_get_temp_dir(), 'PHP\BitTorrent');
        $this->torrent->save($target);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The info part of the torrent is empty
     */
    public function testSaveWithNoInfoBlock() {
        $target = tempnam(sys_get_temp_dir(), 'PHP\BitTorrent');
        $this->torrent->setAnnounce('http://tracker')->save($target);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Could not open file
     */
    public function testSaveToUnwritableFile() {
        $target = uniqid() . DIRECTORY_SEPARATOR . uniqid();

        $torrent = Torrent::createFromPath(__FILE__, 'http://tracker/');
        $torrent->save($target);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage foobar does not exist
     */
    public function testCreateFromTorrentFileWithUnexistingTorrentFile() {
        Torrent::createFromTorrentFile('foobar');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid path: foobar
     */
    public function testCreateFromPathWithInvalidPath() {
        Torrent::createFromPath('foobar', 'http://trackerurl');
    }
}
