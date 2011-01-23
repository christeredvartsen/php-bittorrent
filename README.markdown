# PHP_BitTorrent
**PHP_BitTorrent** is a library that provides PHP classes that can be used to encode/decode to/from the BitTorrent format. It also provides tracker classes that can be used to run your own BitTorrent tracker. The package is not a complete tracker system like for instance btit.

## Requirements
PHP_BitTorrent requires PHP 5.3.x. 

## Installation
No automatick installation is available at the moment. PEAR packages will be available as soon as the project is more complete.

### Manual installation
First, generate the Autoload.php file by running the [phpab](https://github.com/theseer/Autoload) target in the build.xml file:

    christer@aurora:~/php-bittorrent$ ant phpab
    Buildfile: build.xml

    phpab:
         [exec] Autoload file '/home/christer/php-bittorrent/PHP/BitTorrent/Autoload.php' generated.
         [exec] 

    BUILD SUCCESSFUL
    Total time: 0 seconds
    
Then, add the php-bittorrent directory to your include path so you can require the generated Autoload.php script by:

    <?php
    require_once 'PHP/BitTorrent/Autoload.php';

## Using the PHP_BitTorrent API
### Encode PHP variables:

    <?php
    require_once 'PHP/BitTorrent/Autoload.php';
    
    var_dump(PHP_BitTorrent_Encoder::encodeString('Some string')); // string(14) "11:Some string" 
    var_dump(PHP_BitTorrent_Encoder::encodeInteger(42)); // string(4) "i42e"
    var_dump(PHP_BitTorrent_Encoder::encodeList(array(1, 2, 3)); // string(11) "li1ei2ei3ee" 
    var_dump(PHP_BitTorrent_Encoder::encodeDictionary(array('foo' => 'bar', 'bar' => 'foo')); // string(22) "d3:foo3:bar3:bar3:fooe"
    
There is also a convenience method simply called **encode** in the PHP_BitTorrent_Encoder that can be used to encode all encodable variables. Only int, string and arrays can be encoded.
    
### Decode BitTorrent encoded data:

    <?php
    require_once 'PHP/BitTorrent/Autoload.php';
    
    var_dump(PHP_BitTorrent_Decoder::decodeString('11:Some string')); // string(11) "Some string"  
    var_dump(PHP_BitTorrent_Decoder::decodeInteger('i42e')); // int(42)
    var_dump(PHP_BitTorrent_Decoder::decodeList('li1ei2ei3ee'); // array(3) { [0]=> int(1) [1]=> int(2) [2]=> int(3) }
    var_dump(PHP_BitTorrent_Decoder::decodeDictionary('d3:foo3:bar3:bar3:fooe'); // array(2) { ["foo"]=> string(3) "bar" ["bar"]=> string(3) "foo" }

There is also a convenience method called decode that can decode any bittorrent endocded data.

### Decode torrent files
The decoder class also has a method for decoding a torrent file (which is an encoded dictionary):

    <?php
    require_once 'PHP/BitTorrent/Autoload.php';
    
    $decodedFile = PHP_BitTorrent_Decoder::decodeFile('/path/to/file.torrent');
    


## Using the `phpbt` tool
Will add later when I have pushed all of the local files to the git repos at github.