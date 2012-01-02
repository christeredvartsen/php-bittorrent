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
 * @subpackage UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */

/**
 * @package PHP_BitTorrent
 * @subpackage UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class PHP_BitTorrent_TorrentTest extends PHPUnit_Framework_TestCase  {
    /**
     * Torrent object
     *
     * @var PHP_BitTorrent_Torrent
     */
    protected $torrent = null;

    public function setUp() {
        $this->torrent = new PHP_BitTorrent_Torrent();
    }

    public function tearDown() {
        $this->torrent = null;
    }

    public function testSetGetComment() {
        $comment = 'This is my comment';
        $this->torrent->setComment($comment);
        $this->assertSame($comment, $this->torrent->getComment());
    }

    public function testSetGetCreatedBy() {
        $createdBy = 'Some client name';
        $this->torrent->setCreatedBy($createdBy);
        $this->assertSame($createdBy, $this->torrent->getCreatedBy());
    }

    public function testSetGetCreationDate() {
        $timestamp = time();
        $this->torrent->setCreatedAt($timestamp);
        $this->assertSame($timestamp, $this->torrent->getCreatedAt());
    }

    public function testSetGetInfo() {
        $info = array('some' => 'data');
        $this->torrent->setInfo($info);
        $this->assertSame($info, $this->torrent->getInfo());
    }

    public function testSetGetAnnounce() {
        $announce = 'http://tracker/';
        $this->torrent->setAnnounce($announce);
        $this->assertSame($announce, $this->torrent->getAnnounce());
    }

    public function testSetGetPieceLengthExp() {
        $exp = 6;
        $this->torrent->setPieceLengthExp($exp);
        $this->assertSame($exp, $this->torrent->getPieceLengthExp());
    }

    /**
     * @expectedException PHP_BitTorrent_Torrent_Exception
     */
    public function testGetNameWithNoInfoBlockAdded() {
        $this->torrent->getName();
    }

    /**
     * @expectedException PHP_BitTorrent_Torrent_Exception
     */
    public function testGetSizeWithNoInfoBlockAdded() {
        $this->torrent->getSize();
    }

    /**
     * @expectedException PHP_BitTorrent_Torrent_Exception
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

    public function testGetSizeWhenLengtIsPresentInTheInfoBlock() {
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

    public function testLoadFromTorrentFile() {
        $this->torrent->loadFromTorrentFile(__DIR__ . '/_files/valid.torrent');

        $this->assertSame('http://tracker/', $this->torrent->getAnnounce());
        $this->assertSame('Some comment', $this->torrent->getComment());
        $this->assertSame('PHP_BitTorrent', $this->torrent->getCreatedBy());
        $this->assertSame(1295819822, $this->torrent->getCreatedAt());
        $this->assertSame(1546389, $this->torrent->getSize());
        $this->assertSame(269, count($this->torrent->getFileList()));
    }

    public function testLoadFromPathWhenUsingADirectoryAsArgument() {
        $path = __DIR__ . '/_files';
        $this->torrent->loadFromPath($path);
        $this->assertSame('_files', $this->torrent->getName());
        $this->assertSame(18308, $this->torrent->getSize());
        $this->assertSame(3, count($this->torrent->getFileList()));
    }

    public function testLoadFromPathWhenUsingAFileAsArgument() {
        $path = __DIR__ . '/_files/valid.torrent';
        $this->torrent->loadFromPath($path);
        $this->assertSame('valid.torrent', $this->torrent->getName());
        $this->assertSame(18266, $this->torrent->getSize());
        $this->assertSame(1, count($this->torrent->getFileList()));
    }

    public function testSaveTorrent() {
        $path      = __DIR__ . '/_files';
        $announce  = 'http://tracker/';
        $comment   = 'Some comment';
        $createdBy = 'PHPUnit';
        $target    = tempnam(sys_get_temp_dir(), 'PHP_BitTorrent');

        if (!$target) {
            $this->fail('Could not create file: ' . $target);
        }

        $this->torrent->loadFromPath($path)
                      ->setAnnounce($announce)
                      ->setComment($comment)
                      ->setCreatedBy($createdBy)
                      ->save($target);

        // Now load the file and make sure the values are correct
        $torrent = new PHP_BitTorrent_Torrent();
        $torrent->loadFromTorrentFile($target);
        $this->assertSame($announce, $torrent->getAnnounce());
        $this->assertSame($comment, $torrent->getComment());
        $this->assertSame($createdBy, $torrent->getCreatedBy());
        $this->assertSame('_files', $torrent->getName());
        $this->assertSame(18308, $torrent->getSize());
        $this->assertSame(3, count($torrent->getFileList()));

        // Remove the saved file
        unlink($target);
    }

    /**
     * Try to save when no announce has been given. The code we are testing is AFTER the code that
     * checks if the file specified is writeable, so make sure the argument to save() is a file that
     * is writeable.
     */
    public function testSaveWithNoAnnounce() {
        $this->torrent->loadFromPath(__FILE__);
        $this->setExpectedException('PHP_BitTorrent_Torrent_Exception');
        $this->torrent->save('/tmp/file');
    }

    /**
     * @expectedException PHP_BitTorrent_Torrent_Exception
     */
    public function testSaveWithNoInfoBlock() {
        $this->torrent->setAnnounce('http://tracker/')->save('some path');
    }

    public function testSaveToUnwritableFile() {
        $target = uniqid() . DIRECTORY_SEPARATOR . uniqid();

        $this->torrent->loadFromPath(__FILE__)
                      ->setAnnounce('http://tracker/');
        $this->setExpectedException('PHP_BitTorrent_Torrent_Exception');
        $this->torrent->save($target);
    }
}