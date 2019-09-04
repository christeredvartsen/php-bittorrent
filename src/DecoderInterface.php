<?php declare(strict_types=1);
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
     * @param bool $strict If set to true this method will check for certain elements in the
     *                        dictionary.
     * @return array Returns the decoded version of the file as an array
     * @throws InvalidArgumentException
     */
    function decodeFile(string $file, bool $strict = false) : array;

    /**
     * Decode the contents of a file as a string
     *
     * @param string $contents The contents of a torrent file
     * @param bool $strict If set to true this method will check for certain elements in the
     *                        dictionary.
     * @return array Returns the decoded version of the file as an array
     * @throws InvalidArgumentException
     */
    function decodeFileContents(string $contents, bool $strict = false) : array;

    /**
     * Decode any bittorrent encoded string
     *
     * @param string $string The string to decode
     * @return int|string|array Returns the native PHP counterpart of the encoded string
     * @throws InvalidArgumentException
     */
    function decode(string $string);

    /**
     * Decode an encoded PHP integer
     *
     * @param string $integer The integer to decode
     * @return int Returns the decoded integer
     * @throws InvalidArgumentException
     */
    function decodeInteger(string $integer) : int;

    /**
     * Decode an encoded PHP string
     *
     * @param string $string The string to decode
     * @return string Returns the decoded string value
     * @throws InvalidArgumentException
     */
    function decodeString(string $string) : string;

    /**
     * Decode an encoded PHP array
     *
     * @param string $list Encoded list
     * @return array Returns a numerical array
     * @throws InvalidArgumentException
     */
    function decodeList(string $list) : array;

    /**
     * Decode an encoded PHP associative array
     *
     * @param string $dictionary Encoded dictionary
     * @return array Returns an associative array
     * @throws InvalidArgumentException
     */
    function decodeDictionary(string $dictionary) : array;
}
