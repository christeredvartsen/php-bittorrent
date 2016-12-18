<?php
namespace BitTorrent;

use InvalidArgumentException;

interface DecoderInterface {
    /**
     * Decode a file
     *
     * This method can use a strict method that requires certain elements to be present in the
     * encoded file. The two required elements are:
     *
     * - announce
     * - info
     *
     * By default the method does not check for these elements.
     *
     * @param string $file Path to the torrent file we want to decode
     * @param boolean $strict If set to true this method will check for certain elements in the
     *                        dictionary.
     * @return array Returns the decoded version of the file as an array
     * @throws InvalidArgumentException
     */
    function decodeFile($file, $strict = false);

    /**
     * Decode any bittorrent encoded string
     *
     * @param string $string The string to decode
     * @return int|string|array Returns the native PHP counterpart of the encoded string
     * @throws InvalidArgumentException
     */
    function decode($string);

    /**
     * Decode an encoded PHP integer
     *
     * @param string $integer The integer to decode
     * @return int|string Returns the decoded integer (as a string on 32-bit platforms)
     * @throws InvalidArgumentException
     */
    function decodeInteger($integer);

    /**
     * Decode an encoded PHP string
     *
     * @param string $string The string to decode
     * @return string Returns the decoded string value
     * @throws InvalidArgumentException
     */
    function decodeString($string);

    /**
     * Decode an encoded PHP array
     *
     * @param string $list Encoded list
     * @return array Returns a numerical array
     * @throws InvalidArgumentException
     */
    function decodeList($list);

    /**
     * Decode an encoded PHP associative array
     *
     * @param string $dictionary Encoded dictionary
     * @return array Returns an associative array
     * @throws InvalidArgumentException
     */
    function decodeDictionary($dictionary);
}
