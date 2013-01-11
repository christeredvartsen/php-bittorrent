Changelog for PHP BitTorrent
=====================

Version 1.1.0
-------------
__2013-01-11__

* Added parameters for the Encoder. The only parameter currently used it "encodeEmptyArrayAsDictionary" which will cause the encoder to encode empty arrays as "de" (empty dictionary) instead of "le" (empty list).

Version 1.0.0
-------------
__2013-01-05__

* Introduced a getEncodedHash method in the Torrent component to get the URL-encoded hash (Daniel Espendiller)
* Fix issue with integer overflow on 32-bit platforms
* Added method to get the hash of the torrent file (Daniel Espendiller)
* Dictionaries are now sorted by keys before they are encoded (as specified in in http://bittorrent.org/beps/bep\_0003.html)

Version 0.3.0
-------------
__2012-12-08__

* Fixed parse error in composer.json (Daniel Espendiller)
* Added interfaces for the encoder and the decoder (Matt Drollette)
* Fixed autoloader issues in composer.json (Matt Drollette)
* Library is from now on available as a PHAR archive as well
* Allow getting/setting extra fields from the torrent meta data (Matt Drollette)

Version 0.2.0
-------------
__2012-01-04__

* Use proper namespaces
* Changed API to use instance methods instead of static methods

Version 0.0.1
-------------
__2011-01-29__

* Initial release
