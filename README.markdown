PHP_BitTorrent
==============
**PHP_BitTorrent** is a library that provides PHP classes that can be used to encode/decode to/from the BitTorrent format. It also provides tracker classes that can be used to run your own BitTorrent tracker. The package is not a complete tracker system like for instance btit.

Requirements
------------
PHP_BitTorrent requires PHP 5.3.x or above. 

Installation
------------
No automatic installation is available at the moment. PEAR packages will be available as soon as the project is more complete.

**Manual installation**

Fetch the code from https://github.com/christeredvartsen/php-bittorrent and place it in some directory. Add the directory to PHP's include_path so you can require the Autoload.php script by doing:

    <?php
    require_once 'PHP/BitTorrent/Autoload.php';

Using the PHP_BitTorrent API
----------------------------
**Encode PHP variables**

    <?php
    require_once 'PHP/BitTorrent/Autoload.php';
    
    var_dump(PHP_BitTorrent_Encoder::encodeString('Some string')); // string(14) "11:Some string" 
    var_dump(PHP_BitTorrent_Encoder::encodeInteger(42)); // string(4) "i42e"
    var_dump(PHP_BitTorrent_Encoder::encodeList(array(1, 2, 3)); // string(11) "li1ei2ei3ee" 
    var_dump(PHP_BitTorrent_Encoder::encodeDictionary(array('foo' => 'bar', 'bar' => 'foo')); // string(22) "d3:foo3:bar3:bar3:fooe"
    
There is also a convenience method simply called *encode* in the PHP_BitTorrent_Encoder class that can be used to encode all encodable variables. Only integers, strings and arrays can be encoded to the BitTorrent format.
    
**Decode BitTorrent encoded data**

    <?php
    require_once 'PHP/BitTorrent/Autoload.php';
    
    var_dump(PHP_BitTorrent_Decoder::decodeString('11:Some string')); // string(11) "Some string"  
    var_dump(PHP_BitTorrent_Decoder::decodeInteger('i42e')); // int(42)
    var_dump(PHP_BitTorrent_Decoder::decodeList('li1ei2ei3ee'); // array(3) { [0]=> int(1) [1]=> int(2) [2]=> int(3) }
    var_dump(PHP_BitTorrent_Decoder::decodeDictionary('d3:foo3:bar3:bar3:fooe'); // array(2) { ["foo"]=> string(3) "bar" ["bar"]=> string(3) "foo" }

There is also a convenience method called decode that can decode any bittorrent endocded data.

**Decode torrent files**

The decoder class also has a method for decoding a torrent file (which is an encoded dictionary):

    <?php
    require_once 'PHP/BitTorrent/Autoload.php';
    
    $decodedFile = PHP_BitTorrent_Decoder::decodeFile('/path/to/file.torrent');
    
**Create new torrent files and open existing ones**

The **PHP_BitTorrent_Torrent** class represents a torrent file and can be used to create torrent files.

    <?php
    require_once 'PHP/BitTorrent/Autoload.php';
    
    $torrent = new PHP_BitTorrent_Torrent();
    $torrent->setAnnounce('http://tracker/announce.php')
            ->setComment('Some comment')
            ->loadFromPath('/path/to/files')
            ->save('/save/path/file.torrent');
            
The class can also load a torrent file:

    <?php
    require_once 'PHP/BitTorrent/Autoload.php';
    
    $torrent = new PHP_BitTorrent_Torrent();
    $torrent->loadFromTorrentFile('/path/to/file.torrent')
            ->setAnnounce('http://tracker/announce.php') // Override announce in original file
            ->setComment('Some comment') // Override commend in original file
            ->save('/save/path/file.torrent'); // Save to a new file