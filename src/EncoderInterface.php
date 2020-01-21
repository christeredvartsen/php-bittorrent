<?php declare(strict_types=1);
namespace BitTorrent;

use InvalidArgumentException;

interface EncoderInterface {
    /**
     * Encode any encodable variable
     *
     * @param mixed $var The variable to encode. Supports: int, string and array
     * @throws InvalidArgumentException
     * @return string Returns the encoded string
     */
    function encode($var) : string;

    /**
     * Encode an integer
     *
     * @param int $integer The integer to encode
     * @return string Returns the encoded string
     */
    function encodeInteger(int $integer) : string;

    /**
     * Encode a string
     *
     * @param string $string The string to encode
     * @return string Returns the encoded string
     */
    function encodeString(string $string) : string;

    /**
     * Encode a list (numerically indexed array)
     *
     * @param array $list The array to encode
     * @return string Returns the encoded string
     */
    function encodeList(array $list) : string;

    /**
     * Encode a dictionary (associative PHP array)
     *
     * @param array $dictionary The array to encode
     * @return string Returns the encoded string
     */
    function encodeDictionary(array $dictionary) : string;
}
