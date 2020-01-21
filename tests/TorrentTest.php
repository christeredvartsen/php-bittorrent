<?php declare(strict_types=1);
namespace BitTorrent;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use InvalidArgumentException;

/**
 * @coversDefaultClass BitTorrent\Torrent
 */
class TorrentTest extends TestCase {
    private $torrent;

    public function setUp() : void {
        $this->torrent = new Torrent();
    }

    /**
     * @covers ::__construct
     * @covers ::getEncoder
     */
    public function testConstructor() : void {
        $torrent = new Torrent('http://sometracker');
        $this->assertSame('http://sometracker', $torrent->getAnnounceUrl());
        $this->assertInstanceOf(EncoderInterface::class, $torrent->getEncoder());
    }

    /**
     * @covers ::withEncoder
     * @covers ::getEncoder
     */
    public function testSetAndGetEncoder() : void {
        $encoder = $this->createMock(EncoderInterface::class);
        $torrent = (new Torrent('http://sometracker'))->withEncoder($encoder);
        $this->assertInstanceOf(EncoderInterface::class, $torrent->getEncoder());
    }

    public function getDataForSettersAndGetters() : array {
        return [
            'Announce URL' => [
                'getter'  => 'getAnnounceUrl',
                'mutator' => 'withAnnounceUrl',
                'value'   => 'http://tracker',
                'initial' => null,
            ],
            'Piece length exponent' => [
                'getter'  => 'getPieceLengthExp',
                'mutator' => 'withPieceLengthExp',
                'value'   => 6,
                'initial' => 18,
            ],
            'Comment' => [
                'getter'  => 'getComment',
                'mutator' => 'withComment',
                'value'   => 'some comment',
                'initial' => null,
            ],
            'Announce list' => [
                'getter'  => 'getAnnounceList',
                'mutator' => 'withAnnounceList',
                'value'   => ['http://sometracker'],
                'initial' => [],
            ],
            'Created by' => [
                'getter'  => 'getCreatedBy',
                'mutator' => 'withCreatedBy',
                'value'   => 'Some creator',
                'initial' => 'PHP BitTorrent',
            ],
            'Created at' => [
                'getter'  => 'getCreatedAt',
                'mutator' => 'withCreatedAt',
                'value'   => 1567451302,
                'initial' => null,
            ],
            'Info' => [
                'getter'  => 'getInfo',
                'mutator' => 'withInfo',
                'value'   => ['length' => 123, 'name' => 'some name'],
                'initial' => null,
            ],
            'Extra meta' => [
                'getter'  => 'getExtraMeta',
                'mutator' => 'withExtraMeta',
                'value'   => ['foo' => 'bar'],
                'initial' => null,
            ],
        ];
    }

    /**
     * @dataProvider getDataForSettersAndGetters
     * @covers ::withAnnounceUrl
     * @covers ::withPieceLengthExp
     * @covers ::withComment
     * @covers ::withAnnounceList
     * @covers ::withCreatedBy
     * @covers ::withCreatedAt
     * @covers ::withInfo
     * @covers ::withExtraMeta
     *
     * @covers ::getAnnounceUrl
     * @covers ::getPieceLengthExp
     * @covers ::getComment
     * @covers ::getAnnounceList
     * @covers ::getCreatedBy
     * @covers ::getCreatedAt
     * @covers ::getInfo
     * @covers ::getExtraMeta
     */
    public function testSettersAndGetters(string $getter, string $mutator, $value, $initial) : void {
        $this->assertSame($initial, $this->torrent->$getter(), 'Incorrect initial value');
        $torrent = $this->torrent->$mutator($value);
        $this->assertSame($initial, $this->torrent->$getter(), 'Incorrect value after setting');
        $this->assertSame($value, $torrent->$getter(), 'Incorrect value in mutation');
    }

    /**
     * @covers ::getName
     */
    public function testGetNameWithNoInfoBlockAdded() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The info part of the torrent is not set.');
        $this->torrent->getName();
    }

    /**
     * @covers ::getSize
     */
    public function testGetSizeWithNoInfoBlockAdded() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The info part of the torrent is not set.');
        $this->torrent->getSize();
    }

    /**
     * @covers ::getFileList
     */
    public function testGetFileListWithNoInfoBlockAdded() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The info part of the torrent is not set.');
        $this->torrent->getFileList();
    }

    /**
     * @covers ::withInfo
     * @covers ::getName
     */
    public function testGetName() : void {
        $this->assertSame('Some name', $this->torrent->withInfo($info = ['name' => 'Some name'])->getName());
    }

    /**
     * @covers ::withInfo
     * @covers ::getSize
     */
    public function testGetSizeWhenLengthIsPresentInTheInfoBlock() : void {
        $this->assertSame(123, $this->torrent->withInfo(['length' => 123])->getSize());
    }

    /**
     * @covers ::withInfo
     * @covers ::getFileList
     */
    public function testGetFileListWhenInfoBlockOnlyContainsOneFile() : void {
        $fileList = $this->torrent->withInfo(['length' => 123, 'name' => 'some_filename'])->getFileList();
        $this->assertCount(1, $fileList);
        $this->assertSame('some_filename', $fileList[0]);
    }

    /**
     * @covers ::withInfo
     * @covers ::getFileList
     */
    public function testGetFileList() : void {
        $files = [
            ['length' => 12, 'path' => ['path', 'file.php']],
            ['length' => 32, 'path' => ['path2', 'file2.php']],
            ['length' => 123, 'path' => ['file.php']],
        ];
        $this->assertSame($files, $this->torrent->withInfo(['files' => $files])->getFileList());
    }

    /**
     * @covers ::withInfo
     * @covers ::getSize
     */
    public function testGetSizeWhenInfoBlockHasSeveralFiles() : void {
        $files = [
            ['length' =>  12, 'path' => ['path', 'file.php']],
            ['length' =>  32, 'path' => ['path2', 'file2.php']],
            ['length' => 123, 'path' => ['file.php']],
        ];
        $this->assertEquals(167, $this->torrent->withInfo(['files' => $files])->getSize());
    }

    /**
     * @covers ::createFromTorrentFile
     * @covers ::createFromDictionary
     * @covers ::getAnnounceUrl
     * @covers ::getComment
     * @covers ::getCreatedBy
     * @covers ::getCreatedAt
     * @covers ::getSize
     * @covers ::getFileList
     */
    public function testCreateFromTorrentFile() : void {
        $torrent = Torrent::createFromTorrentFile(__DIR__ . '/_files/valid.torrent', new Decoder());

        $this->assertSame('http://trackerurl', $torrent->getAnnounceUrl());
        $this->assertSame('This is a comment', $torrent->getComment());
        $this->assertSame('PHP BitTorrent', $torrent->getCreatedBy());
        $this->assertSame(1323713688, $torrent->getCreatedAt());
        $this->assertEquals(30243, $torrent->getSize());
        $this->assertSame(5, count($torrent->getFileList()));
    }

    /**
     * @covers ::createFromString
     * @covers ::createFromDictionary
     * @covers ::getAnnounceUrl
     * @covers ::getComment
     * @covers ::getCreatedBy
     * @covers ::getCreatedAt
     * @covers ::getSize
     * @covers ::getFileList
     */
    public function testCreateFromTorrentFileString() : void {
        $torrent = Torrent::createFromString(file_get_contents(__DIR__ . '/_files/valid.torrent'), new Decoder());

        $this->assertSame('http://trackerurl', $torrent->getAnnounceUrl());
        $this->assertSame('This is a comment', $torrent->getComment());
        $this->assertSame('PHP BitTorrent', $torrent->getCreatedBy());
        $this->assertSame(1323713688, $torrent->getCreatedAt());
        $this->assertEquals(30243, $torrent->getSize());
        $this->assertSame(5, count($torrent->getFileList()));
    }

    /**
     * @covers ::createFromTorrentFile
     * @covers ::createFromDictionary
     * @covers ::getAnnounceUrl
     * @covers ::getFileList
     */
    public function testCreateFromTorrentFileWithLists() : void {
        $torrent = Torrent::createFromTorrentFile(__DIR__ . '/_extra_files/extra.torrent', new Decoder());
        $announceList = [
            [
                'http://tracker/',
                'http://tracker2/',
                'http://tracker3/',
            ]
        ];

        $this->assertSame('http://tracker/', $torrent->getAnnounceUrl());
        $this->assertEquals($announceList, $torrent->getAnnounceList());
        $this->assertSame(1, count($torrent->getFileList()));
    }

    /**
     * @covers ::createFromTorrentFile
     * @covers ::getExtraMeta
     */
    public function testCreateFromTorrentFileWithExtra() : void {
        $torrent = Torrent::createFromTorrentFile(__DIR__ . '/_extra_files/extra.torrent', new Decoder());
        $webSeeds = [
            'url-list' =>
                [
                    'http://seed/',
                    'http://seed2/',
                    'http://seed3/',
                ]
        ];

        $this->assertEquals($webSeeds, $torrent->getExtraMeta());
    }

    /**
     * @covers ::createFromPath
     * @covers ::getAnnounceUrl
     * @covers ::getName
     * @covers ::getSize
     * @covers ::getFileList
     */
    public function testCreateFromPathWhenUsingADirectoryAsArgument() : void {
        $path = __DIR__ . '/_files';
        $trackerUrl = 'http://trackerurl';
        $torrent = Torrent::createFromPath($path, $trackerUrl);

        $this->assertSame($trackerUrl, $torrent->getAnnounceUrl());
        $this->assertSame('_files', $torrent->getName());
        $this->assertEquals(902910, $torrent->getSize());
        $this->assertSame(7, count($torrent->getFileList()));
    }

    /**
     * @covers ::createFromPath
     * @covers ::getAnnounceUrl
     * @covers ::getName
     * @covers ::getSize
     * @covers ::getFileList
     */
    public function testCreateFromPathWhenUsingAFileAsArgument() : void {
        $path = __DIR__ . '/_files/valid.torrent';
        $trackerUrl = 'http://trackerurl';
        $torrent = Torrent::createFromPath($path, $trackerUrl);

        $this->assertSame($trackerUrl, $torrent->getAnnounceUrl());
        $this->assertSame('valid.torrent', $torrent->getName());
        $this->assertSame(440, $torrent->getSize());
        $this->assertSame(1, count($torrent->getFileList()));
    }

    /**
     * @covers ::createFromPath
     * @covers ::withComment
     * @covers ::withCreatedBy
     * @covers ::withAnnounceList
     * @covers ::save
     * @covers ::createFromTorrentFile
     * @covers ::getAnnounceUrl
     * @covers ::getComment
     * @covers ::getCreatedBy
     * @covers ::getName
     * @covers ::getSize
     * @covers ::getFileList
     * @covers ::getAnnounceList
     * @covers ::getInfoPart
     */
    public function testSaveTorrent() : void {
        $path         = __DIR__ . '/_files';
        $announce     = 'http://tracker/';
        $announceList = [['http://tracker2'], ['http://tracker3']];
        $comment      = 'Some comment';
        $createdBy    = 'PHPUnit';
        $target       = tempnam(sys_get_temp_dir(), 'PHP\BitTorrent');

        if (!$target) {
            $this->fail('Could not create file: ' . $target);
        }

        $torrent = Torrent::createFromPath($path, $announce)
            ->withComment($comment)
            ->withCreatedBy($createdBy)
            ->withAnnounceList($announceList);

        $this->assertTrue($torrent->save($target));

        $torrent = Torrent::createFromTorrentFile($target, new Decoder());

        $this->assertSame($announce, $torrent->getAnnounceUrl());
        $this->assertSame($comment, $torrent->getComment());
        $this->assertSame($createdBy, $torrent->getCreatedBy());
        $this->assertSame('_files', $torrent->getName());
        $this->assertEquals(902910, $torrent->getSize());
        $this->assertSame(7, count($torrent->getFileList()));
        $this->assertSame($announceList, $torrent->getAnnounceList());

        unlink($target);
    }

    /**
     * @covers ::createFromPath
     * @covers ::withExtraMeta
     * @covers ::save
     * @covers ::createFromTorrentFile
     * @covers ::getExtraMeta
     */
    public function testSaveWithExtra() : void {
        $path      = __DIR__ . '/_files';
        $announce  = 'http://tracker/';
        $target    = tempnam(sys_get_temp_dir(), 'PHP\BitTorrent');

        if (!$target) {
            $this->fail('Could not create file: ' . $target);
        }

        $extra = [
            'url-list' => [
                'http://seed/',
                'http://seed2/',
                'http://seed3/',
            ],
        ];

        $torrent = Torrent::createFromPath($path, $announce)->withExtraMeta($extra);
        $torrent->save($target);

        $torrent = Torrent::createFromTorrentFile($target, new Decoder());

        $this->assertEquals($extra, $torrent->getExtraMeta());

        unlink($target);
    }

    /**
     * @covers ::createFromPath
     * @covers ::withExtraMeta
     * @covers ::save
     */
    public function testSaveWithInvalidExtra() : void {
        $path      = __DIR__ . '/_files';
        $announce  = 'http://tracker/';
        $target    = tempnam(sys_get_temp_dir(), 'PHP\BitTorrent');

        if (!$target) {
            $this->fail('Could not create file: ' . $target);
        }

        $extra = ['announce' => 'http://extratracker'];

        $torrent = Torrent::createFromPath($path, $announce)
            ->withExtraMeta($extra);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Duplicate key in extra meta info');
        $torrent->save($target);
    }

    /**
     * @covers ::save
     */
    public function testSaveWithNoAnnounce() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Announce URL is missing');
        $this->torrent->save(tempnam(sys_get_temp_dir(), 'PHP\BitTorrent'));
    }

    /**
     * @covers ::withAnnounceUrl
     * @covers ::save
     * @covers ::getInfoPart
     */
    public function testSaveWithNoInfoBlock() : void {
        $torrent = $this->torrent->withAnnounceUrl('http://tracker');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The info part of the torrent is not set.');
        $torrent->save(tempnam(sys_get_temp_dir(), 'PHP\BitTorrent'));
    }

    /**
     * @covers ::createFromPath
     * @covers ::save
     */
    public function testSaveToUnwritableFile() : void {
        $torrent = Torrent::createFromPath(__FILE__, 'http://tracker/');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not open file');
        $torrent->save(uniqid() . DIRECTORY_SEPARATOR . uniqid());
    }

    /**
     * @covers ::createFromTorrentFile
     */
    public function testCreateFromTorrentFileWithUnexistingTorrentFile() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('foobar does not exist');
        Torrent::createFromTorrentFile('foobar', new Decoder());
    }

    /**
     * @covers ::createFromPath
     */
    public function testCreateFromPathWithInvalidPath() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid path: foobar');
        Torrent::createFromPath('foobar', 'http://trackerurl');
    }

    /**
     * @covers ::getHash
     */
    public function testThrowsExceptionWhenTryingToGenerateHashWithEmptyTorrentFile() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The info part of the torrent is not set.');
        $this->torrent->getHash();
    }

    /**
     * @covers ::getEncodedHash
     */
    public function testThrowsExceptionWhenTryingToGenerateEncodedHashWithEmptyTorrentFile() : void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The info part of the torrent is not set.');
        $this->torrent->getEncodedHash();
    }

    /**
     * @covers ::getHash
     */
    public function testGetHash() : void {
        $this->assertSame(
            'c717bfd30211a5e36c94cabaab3b3ca0dc89f91a',
            Torrent::createFromTorrentFile(__DIR__ . '/_files/valid.torrent', new Decoder())->getHash()
        );
    }

    /**
     * @covers ::getEncodedHash
     * @covers ::getHash
     */
    public function testGetEncodedHash() : void {
        $this->assertSame(
            '%C7%17%BF%D3%02%11%A5%E3l%94%CA%BA%AB%3B%3C%A0%DC%89%F9%1A',
            Torrent::createFromTorrentFile(__DIR__ . '/_files/valid.torrent', new Decoder())->getEncodedHash()
        );
    }

    /**
     * @covers ::getSize
     */
    public function testGetSizeWithLargeValues() : void {
        $decoder = new Decoder();
        $this->assertSame(6442450944, Torrent::createFromTorrentFile(__DIR__ . '/_files/large_files.torrent', $decoder)->getSize());
        $this->assertSame(5368709120, Torrent::createFromTorrentFile(__DIR__ . '/_files/large_file.img.torrent', $decoder)->getSize());
    }

    /**
     * @covers ::isPrivate
     */
    public function testIsPrivateWhenFlagDoesNotExist() : void {
        $this->assertFalse(Torrent::createFromTorrentFile(__DIR__ . '/_files/large_files.torrent', new Decoder())->isPrivate());
    }

    /**
     * @covers ::isPrivate
     */
    public function testIsPrivateWhenItExistsAndIs1() : void {
        $this->assertTrue(Torrent::createFromTorrentFile(__DIR__ . '/_files/file_with_private_set_to_1.torrent', new Decoder())->isPrivate());
    }

    /**
     * @covers ::isPrivate
     */
    public function testIsPrivateWhenItExistsAndIsNot1() : void {
        $this->assertFalse(Torrent::createFromTorrentFile(__DIR__ . '/_files/file_with_private_set_to_0.torrent', new Decoder())->isPrivate());
    }
}
