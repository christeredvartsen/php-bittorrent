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
 * @package UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/php-bittorrent
 */

namespace PHP\BitTorrent;

/**
 * @package UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/php-bittorrent
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

    /**
     * @covers PHP\BitTorrent\Torrent::__construct
     * @covers PHP\BitTorrent\Torrent::setAnnounce
     * @covers PHP\BitTorrent\Torrent::getAnnounce
     */
    public function testConstructor() {
        $announce = 'http://tracker/';
        $torrent = new Torrent($announce);
        $this->assertSame($announce, $torrent->getAnnounce());
    }

    /**
     * @covers PHP\BitTorrent\Torrent::setComment
     * @covers PHP\BitTorrent\Torrent::getComment
     */
    public function testSetGetComment() {
        $comment = 'This is my comment';
        $this->assertSame($this->torrent, $this->torrent->setComment($comment));
        $this->assertSame($comment, $this->torrent->getComment());
    }

    /**
     * @covers PHP\BitTorrent\Torrent::setCreatedBy
     * @covers PHP\BitTorrent\Torrent::getCreatedBy
     */
    public function testSetGetCreatedBy() {
        $createdBy = 'Some client name';
        $this->assertSame($this->torrent, $this->torrent->setCreatedBy($createdBy));
        $this->assertSame($createdBy, $this->torrent->getCreatedBy());
    }

    /**
     * @covers PHP\BitTorrent\Torrent::setCreatedAt
     * @covers PHP\BitTorrent\Torrent::getCreatedAt
     */
    public function testSetGetCreationDate() {
        $timestamp = time();
        $this->assertSame($this->torrent, $this->torrent->setCreatedAt($timestamp));
        $this->assertSame($timestamp, $this->torrent->getCreatedAt());
    }

    /**
     * @covers PHP\BitTorrent\Torrent::setInfo
     * @covers PHP\BitTorrent\Torrent::getInfo
     */
    public function testSetGetInfo() {
        $info = array('some' => 'data');
        $this->assertSame($this->torrent, $this->torrent->setInfo($info));
        $this->assertSame($info, $this->torrent->getInfo());
    }

    /**
     * @covers PHP\BitTorrent\Torrent::setAnnounce
     * @covers PHP\BitTorrent\Torrent::getAnnounce
     */
    public function testSetGetAnnounce() {
        $announce = 'http://tracker/';
        $this->assertSame($this->torrent, $this->torrent->setAnnounce($announce));
        $this->assertSame($announce, $this->torrent->getAnnounce());
    }

    /**
     * @covers PHP\BitTorrent\Torrent::setAnnounceList
     * @covers PHP\BitTorrent\Torrent::getAnnounceList
     */
    public function testSetGetAnnounceList() {
        $announceList = array('http://tracker1/', 'http://tracker2/');
        $this->assertSame($this->torrent, $this->torrent->setAnnounceList($announceList));
        $this->assertSame($announceList, $this->torrent->getAnnounceList());
    }

    /**
     * @covers PHP\BitTorrent\Torrent::setPieceLengthExp
     * @covers PHP\BitTorrent\Torrent::getPieceLengthExp
     */
    public function testSetGetPieceLengthExp() {
        $exp = 6;
        $this->assertSame($this->torrent, $this->torrent->setPieceLengthExp($exp));
        $this->assertSame($exp, $this->torrent->getPieceLengthExp());
    }

    /**
     * @expectedException RuntimeException
     * @covers PHP\BitTorrent\Torrent::getName
     */
    public function testGetNameWithNoInfoBlockAdded() {
        $this->torrent->getName();
    }

    /**
     * @expectedException RuntimeException
     * @covers PHP\BitTorrent\Torrent::getSize
     */
    public function testGetSizeWithNoInfoBlockAdded() {
        $this->torrent->getSize();
    }

    /**
     * @expectedException RuntimeException
     * @covers PHP\BitTorrent\Torrent::getFileList
     */
    public function testGetFileListWithNoInfoBlockAdded() {
        $this->torrent->getFileList();
    }

    /**
     * @covers PHP\BitTorrent\Torrent::setInfo
     * @covers PHP\BitTorrent\Torrent::getName
     */
    public function testGetName() {
        $name = 'Some name';
        $info = array('name' => $name);
        $this->torrent->setInfo($info);
        $this->assertSame($name, $this->torrent->getName());
    }

    /**
     * @covers PHP\BitTorrent\Torrent::setInfo
     * @covers PHP\BitTorrent\Torrent::getSize
     */
    public function testGetSizeWhenLengthIsPresentInTheInfoBlock() {
        $length = 123;
        $info = array('length' => $length);
        $this->torrent->setInfo($info);
        $this->assertSame($length, $this->torrent->getSize());
    }

    /**
     * @covers PHP\BitTorrent\Torrent::setInfo
     * @covers PHP\BitTorrent\Torrent::getFileList
     */
    public function testGetFileListWhenInfoBlockOnlyContainsOneFile() {
        $name = 'some_filename';
        $info = array('length' => 123, 'name' => $name);
        $this->torrent->setInfo($info);
        $this->assertSame($name, $this->torrent->getFileList());
    }

    /**
     * @covers PHP\BitTorrent\Torrent::setInfo
     * @covers PHP\BitTorrent\Torrent::getFileList
     */
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

    /**
     * @covers PHP\BitTorrent\Torrent::setInfo
     * @covers PHP\BitTorrent\Torrent::getSize
     * @covers PHP\BitTorrent\Torrent::add
     */
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

    /**
     * @covers PHP\BitTorrent\Torrent::createFromTorrentFile
     * @covers PHP\BitTorrent\Torrent::getAnnounce
     * @covers PHP\BitTorrent\Torrent::getComment
     * @covers PHP\BitTorrent\Torrent::getCreatedBy
     * @covers PHP\BitTorrent\Torrent::getCreatedAt
     * @covers PHP\BitTorrent\Torrent::getSize
     * @covers PHP\BitTorrent\Torrent::add
     * @covers PHP\BitTorrent\Torrent::getFileList
     */
    public function testCreateFromTorrentFile() {
        $torrent = Torrent::createFromTorrentFile(__DIR__ . '/_files/valid.torrent');

        $this->assertSame('http://trackerurl', $torrent->getAnnounce());
        $this->assertSame('This is a comment', $torrent->getComment());
        $this->assertSame('PHP BitTorrent', $torrent->getCreatedBy());
        $this->assertSame(1323713688, $torrent->getCreatedAt());
        $this->assertSame(30243, $torrent->getSize());
        $this->assertSame(5, count($torrent->getFileList()));
    }

    /**
     * @covers PHP\BitTorrent\Torrent::createFromTorrentFile
     * @covers PHP\BitTorrent\Torrent::getAnnounce
     * @covers PHP\BitTorrent\Torrent::getFileList
     */
    public function testCreateFromTorrentFileWithLists() {
        $torrent = Torrent::createFromTorrentFile(__DIR__ . '/_extra_files/extra.torrent');

        // we expect an array of arrays, according to the spec
        $announceList = array(
            array(
                'http://tracker/',
                'http://tracker2/',
                'http://tracker3/'
            )
        );

        $this->assertSame('http://tracker/', $torrent->getAnnounce());
        $this->assertEquals($announceList, $torrent->getAnnounceList());
        $this->assertSame(1, count($torrent->getFileList()));
    }

    /**
     * @covers PHP\BitTorrent\Torrent::createFromTorrentFile
     * @covers PHP\BitTorrent\Torrent::getExtraMeta
     */
    public function testCreateFromTorrentFileWithExtra() {
        $torrent = Torrent::createFromTorrentFile(__DIR__ . '/_extra_files/extra.torrent');

        $webSeeds = array(
            'url-list' =>
                array(
                    'http://seed/',
                    'http://seed2/',
                    'http://seed3/'
                )
        );

        $this->assertEquals($webSeeds, $torrent->getExtraMeta());
    }

    /**
     * @covers PHP\BitTorrent\Torrent::createFromPath
     * @covers PHP\BitTorrent\Torrent::getAnnounce
     * @covers PHP\BitTorrent\Torrent::getName
     * @covers PHP\BitTorrent\Torrent::getSize
     * @covers PHP\BitTorrent\Torrent::add
     * @covers PHP\BitTorrent\Torrent::getFileList
     */
    public function testCreateFromPathWhenUsingADirectoryAsArgument() {
        $path = __DIR__ . '/_files';
        $trackerUrl = 'http://trackerurl';
        $torrent = Torrent::createFromPath($path, $trackerUrl);

        $this->assertSame($trackerUrl, $torrent->getAnnounce());
        $this->assertSame('_files', $torrent->getName());
        $this->assertSame(902004, $torrent->getSize());
        $this->assertSame(5, count($torrent->getFileList()));
    }

    /**
     * @covers PHP\BitTorrent\Torrent::createFromPath
     * @covers PHP\BitTorrent\Torrent::getAnnounce
     * @covers PHP\BitTorrent\Torrent::getName
     * @covers PHP\BitTorrent\Torrent::getSize
     * @covers PHP\BitTorrent\Torrent::add
     * @covers PHP\BitTorrent\Torrent::getFileList
     */
    public function testCreateFromPathWhenUsingAFileAsArgument() {
        $path = __DIR__ . '/_files/valid.torrent';
        $trackerUrl = 'http://trackerurl';
        $torrent = Torrent::createFromPath($path, $trackerUrl);

        $this->assertSame($trackerUrl, $torrent->getAnnounce());
        $this->assertSame('valid.torrent', $torrent->getName());
        $this->assertSame(440, $torrent->getSize());
        $this->assertSame(1, count($torrent->getFileList()));
    }

    /**
     * @covers PHP\BitTorrent\Torrent::createFromPath
     * @covers PHP\BitTorrent\Torrent::setComment
     * @covers PHP\BitTorrent\Torrent::setCreatedBy
     * @covers PHP\BitTorrent\Torrent::setAnnounceList
     * @covers PHP\BitTorrent\Torrent::save
     * @covers PHP\BitTorrent\Torrent::createFromTorrentFile
     * @covers PHP\BitTorrent\Torrent::getAnnounce
     * @covers PHP\BitTorrent\Torrent::getComment
     * @covers PHP\BitTorrent\Torrent::getCreatedBy
     * @covers PHP\BitTorrent\Torrent::getName
     * @covers PHP\BitTorrent\Torrent::getSize
     * @covers PHP\BitTorrent\Torrent::add
     * @covers PHP\BitTorrent\Torrent::getFileList
     * @covers PHP\BitTorrent\Torrent::getAnnounceList
     */
    public function testSaveTorrent() {
        $path         = __DIR__ . '/_files';
        $announce     = 'http://tracker/';
        $announceList = array(array('http://tracker2'), array('http://tracker3'));;
        $comment      = 'Some comment';
        $createdBy    = 'PHPUnit';
        $target       = tempnam(sys_get_temp_dir(), 'PHP\BitTorrent');

        if (!$target) {
            $this->fail('Could not create file: ' . $target);
        }

        $torrent = Torrent::createFromPath($path, $announce);
        $torrent->setComment($comment)
                ->setCreatedBy($createdBy)
                ->setAnnounceList($announceList)
                ->save($target);

        // Now load the file and make sure the values are correct
        $torrent = Torrent::createFromTorrentFile($target);

        $this->assertSame($announce, $torrent->getAnnounce());
        $this->assertSame($comment, $torrent->getComment());
        $this->assertSame($createdBy, $torrent->getCreatedBy());
        $this->assertSame('_files', $torrent->getName());
        $this->assertSame(902004, $torrent->getSize());
        $this->assertSame(5, count($torrent->getFileList()));
        $this->assertSame($announceList, $torrent->getAnnounceList());

        // Remove the saved file
        unlink($target);
    }

    /**
     * @covers PHP\BitTorrent\Torrent::createFromPath
     * @covers PHP\BitTorrent\Torrent::setExtraMeta
     * @covers PHP\BitTorrent\Torrent::save
     * @covers PHP\BitTorrent\Torrent::createFromTorrentFile
     * @covers PHP\BitTorrent\Torrent::getExtraMeta
     */
    public function testSaveWithExtra() {
        $path      = __DIR__ . '/_files';
        $announce  = 'http://tracker/';
        $target    = tempnam(sys_get_temp_dir(), 'PHP\BitTorrent');

        if (!$target) {
            $this->fail('Could not create file: ' . $target);
        }

        $extra = array(
            'url-list' =>
            array(
                'http://seed/',
                'http://seed2/',
                'http://seed3/'
            )
        );

        $torrent = Torrent::createFromPath($path, $announce);
        $torrent->setExtraMeta($extra)
                ->save($target);

        // Now load the file and make sure the values are correct
        $torrent = Torrent::createFromTorrentFile($target);

        $this->assertEquals($extra, $torrent->getExtraMeta());

        // Remove the saved file
        unlink($target);
    }

    /**
     * Try to save extra fields with keys that already exist
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Duplicate key in extra meta info
     * @covers PHP\BitTorrent\Torrent::createFromPath
     * @covers PHP\BitTorrent\Torrent::setExtraMeta
     * @covers PHP\BitTorrent\Torrent::save
     */
    public function testSaveWithInvalidExtra() {
        $path      = __DIR__ . '/_files';
        $announce  = 'http://tracker/';
        $target    = tempnam(sys_get_temp_dir(), 'PHP\BitTorrent');

        if (!$target) {
            $this->fail('Could not create file: ' . $target);
        }

        $extra = array('announce' => 'http://extratracker');

        $torrent = Torrent::createFromPath($path, $announce);
        $torrent->setExtraMeta($extra)
                ->save($target);

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
     * @covers PHP\BitTorrent\Torrent::save
     */
    public function testSaveWithNoAnnounce() {
        $target = tempnam(sys_get_temp_dir(), 'PHP\BitTorrent');
        $this->torrent->save($target);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The info part of the torrent is empty
     * @covers PHP\BitTorrent\Torrent::setAnnounce
     * @covers PHP\BitTorrent\Torrent::save
     */
    public function testSaveWithNoInfoBlock() {
        $target = tempnam(sys_get_temp_dir(), 'PHP\BitTorrent');
        $this->torrent->setAnnounce('http://tracker')
                      ->save($target);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Could not open file
     * @covers PHP\BitTorrent\Torrent::createFromPath
     * @covers PHP\BitTorrent\Torrent::save
     */
    public function testSaveToUnwritableFile() {
        $target = uniqid() . DIRECTORY_SEPARATOR . uniqid();

        $torrent = Torrent::createFromPath(__FILE__, 'http://tracker/');
        $torrent->save($target);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage foobar does not exist
     * @covers PHP\BitTorrent\Torrent::createFromTorrentFile
     */
    public function testCreateFromTorrentFileWithUnexistingTorrentFile() {
        Torrent::createFromTorrentFile('foobar');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid path: foobar
     * @covers PHP\BitTorrent\Torrent::createFromPath
     */
    public function testCreateFromPathWithInvalidPath() {
        Torrent::createFromPath('foobar', 'http://trackerurl');
    }

    /**
     * @expectedException RuntimeException
     * @covers PHP\BitTorrent\Torrent::getHash
     */
    public function testThrowsExceptionWhenTryingToGenerateHashWithEmptyTorrentFile() {
        $this->torrent->getHash();
    }

    /**
     * @covers PHP\BitTorrent\Torrent::getHash
     */
    public function testGetHash() {
        $torrent = Torrent::createFromTorrentFile(__DIR__ . '/_files/valid.torrent');
        $this->assertSame('%C7%17%BF%D3%02%11%A5%E3l%94%CA%BA%AB%3B%3C%A0%DC%89%F9%1A', $torrent->getHash());
    }

    /**
     * @covers PHP\BitTorrent\Torrent::getSize
     * @covers PHP\BitTorrent\Torrent::add
     */
    public function testGetSizeWithLargeValues() {
        $torrent1 = Torrent::createFromTorrentFile(__DIR__ . '/_files/large_files.torrent');
        $torrent2 = Torrent::createFromTorrentFile(__DIR__ . '/_files/large_file.img.torrent');

        $this->assertEquals("6442450944", $torrent1->getSize());
        $this->assertEquals("5368709120", $torrent2->getSize());
    }
}
