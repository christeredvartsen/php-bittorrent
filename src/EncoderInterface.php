<?php
/**
 * This file is part of the PHP BitTorrent package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace BitTorrent;

use InvalidArgumentException;

/**
 * Interface for encoders
 *
 * @package Encoder
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
interface EncoderInterface {
    /**
     * Set a parameter
     *
     * @param string $key The key to set
     * @param mixed $value The value to set
     * @return EncoderInterface
     */
    function setParam($key, $value);

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
