# PHP BitTorrent
**PHP BitTorrent** is a set of components that can be used to interact with torrent files (read+write) and encode/decode to/from the [BitTorrent format](https://wiki.theory.org/index.php/BitTorrentSpecification).

[![Current Build Status](https://github.com/christeredvartsen/php-bittorrent/workflows/Build%20and%20test/badge.svg)](https://github.com/christeredvartsen/php-bittorrent/actions)

## Requirements
PHP BitTorrent requires PHP 7.2 or above.

## Installation
PHP BitTorrent can be installed using [Composer](https://getcomposer.org):

    composer require christeredvartsen/php-bittorrent ^2.0

## Using the PHP BitTorrent API
### Encode PHP variables

```php
<?php
require 'vendor/autoload.php';

$encoder = new BitTorrent\Encoder();

var_dump($encoder->encodeString('Some string')); // string(14) "11:Some string"
var_dump($encoder->encodeInteger(42)); // string(4) "i42e"
var_dump($encoder->encodeList([1, 2, 3]); // string(11) "li1ei2ei3ee"
var_dump($encoder->encodeDictionary(['foo' => 'bar', 'bar' => 'foo']); // string(22) "d3:foo3:bar3:bar3:fooe"
```

There is also a convenience method simply called `encode` in the `BitTorrent\Encoder` class that can be used to encode all encodable variables (integers, strings and arrays).

### Decode BitTorrent encoded data

```php
<?php
require 'vendor/autoload.php';

$decoder = new BitTorrent\Decoder();

var_dump($decoder->decodeString('11:Some string')); // string(11) "Some string"
var_dump($decoder->decodeInteger('i42e')); // int(42)
var_dump($decoder->decodeList('li1ei2ei3ee'); // array(3) { [0]=> int(1) [1]=> int(2) [2]=> int(3) }
var_dump($decoder->decodeDictionary('d3:foo3:bar3:bar3:fooe'); // array(2) { ["foo"]=> string(3) "bar" ["bar"]=> string(3) "foo" }
```

There is also a convenience method called `decode` that can decode any BitTorrent encoded data.

### Decode torrent files

The decoder class also has a method for decoding a torrent file (which is an encoded dictionary):

```php
<?php
require 'vendor/autoload.php';

$decoder = new BitTorrent\Decoder();
$decodedFile = $decoder->decodeFile('/path/to/file.torrent');
```

### Create new torrent files and open existing ones

The `BitTorrent\Torrent` class represents a torrent file and can be used to create torrent files.

```php
<?php
require 'vendor/autoload.php';

$torrent = BitTorrent\Torrent::createFromPath('/path/to/files', 'http://tracker/announce.php')
    ->withComment('Some comment');

$torrent->save('/save/to/path/file.torrent');
```

The class can also load a torrent file:

```php
<?php
require 'vendor/autoload.php';

$torrent = BitTorrent\Torrent::createFromTorrentFile('/path/to/file.torrent')
    ->withAnnounce('http://tracker/announce.php') // Override announce in original file
    ->withComment('Some comment'); // Override commend in original file

$torrent->save('/save/to/path/file.torrent'); // Save to a new file
```

## License
Licensed under the MIT License.

See [LICENSE](LICENSE) file.
