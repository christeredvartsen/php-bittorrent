<?php
namespace BitTorrent;

use InvalidArgumentException;

interface EncoderInterface {
    /**
     * Encode any encodable variable
     *
     * @param int|string|array $var The variable to encode
     * @return string Returns the encoded string
     * @throws InvalidArgumentException
     */
    function encode($var);

    /**
     * Encode an integer
     *
     * @param int|string $integer The integer to encode. Strings are supported on 32-bit platforms
     * @return string Returns the encoded string
     * @throws InvalidArgumentException
     */
    function encodeInteger($integer);

    /**
     * Encode a string
     *
     * @param string $string The string to encode
     * @return string Returns the encoded string
     * @throws InvalidArgumentException
     */
    function encodeString($string);

    /**
     * Encode a list (numerically indexed array)
     *
     * @param array $list The array to encode
     * @return string Returns the encoded string
     * @throws InvalidArgumentException
     */
    function encodeList(array $list);

    /**
     * Encode a dictionary (associative PHP array)
     *
     * @param array $dictionary The array to encode
     * @return string Returns the encoded string
     * @throws InvalidArgumentException
     */
    function encodeDictionary(array $dictionary);
}
