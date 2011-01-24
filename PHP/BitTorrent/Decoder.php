<?php
/**
 * PHP_BitTorrent
 *
 * Copyright (c) 2011 Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package PHP_BitTorrent
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */

/**
 * Decode bittorrent strings to it's PHP variable counterpart
 *
 * @package PHP_BitTorrent
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class PHP_BitTorrent_Decoder {
    /**
     * Decode a file
     *
     * @param string $file Path to the torrent file we want to decode
     * @param boolean $strict If set to true this method will check for certain elements in the dictionary.
     * @return array
     * @throws PHP_BitTorrent_Decoder_Exception
     */
    static public function decodeFile($file, $strict = false) {
        if (!is_readable($file)) {
            throw new PHP_BitTorrent_Decoder_Exception('File ' . $file . ' does not exist or can not be read.');
        }

        $dictionary = static::decodeDictionary(file_get_contents($file, true));

        if ($strict) {
            if (!isset($dictionary['announce']) || !is_string($dictionary['announce'])) {
                throw new PHP_BitTorrent_Decoder_Exception('Missing "announce" key.');
            } else if (!isset($dictionary['info']) || !is_array($dictionary['info'])) {
                throw new PHP_BitTorrent_Decoder_Exception('Missing "info" key.');
            }
        }

        return $dictionary;
    }

    /**
     * Decode any bittorrent encoded string
     *
     * @param string $string
     * @return mixed
     * @throws PHP_BitTorrent_Decoder_Exception
     */
    static public function decode($string) {
        if ($string[0] === 'i') {
            return static::decodeInteger($string);
        } else if ($string[0] === 'l') {
            return static::decodeList($string);
        } else if ($string[0] === 'd') {
            return static::decodeDictionary($string);
        } else if (preg_match('/^\d+:/', $string)) {
            return static::decodeString($string);
        }

        throw new PHP_BitTorrent_Decoder_Exception('Parameter is not correctly encoded.');
    }

    /**
     * Decode an encoded PHP integer
     *
     * @param string $integer
     * @return int
     * @throws PHP_BitTorrent_Decoder_Exception
     */
    static public function decodeInteger($integer) {
        if ($integer[0] !== 'i' || (!$ePos = strpos($integer, 'e'))) {
            throw new PHP_BitTorrent_Decoder_Exception('Invalid integer. Inteers must start wth "i" and end with "e".');
        }

        $int = substr($integer, 1, ($ePos - 1));
        $intLen = strlen($int);

        if (($int[0] === '0' && $intLen > 1) || ($int[0] === '-' && $int[1] === '0') || !is_numeric($int)) {
            throw new PHP_BitTorrent_Decoder_Exception('Invalid integer value.');
        }

        return (int) $int;
    }

    /**
     * Decode an encoded PHP string
     *
     * @param string $string
     * @return string
     * @throws PHP_BitTorrent_Decoder_Exception
     */
    static public function decodeString($string) {
        $stringParts = explode(':', $string, 2);

        // The string must have two parts
        if (count($stringParts) !== 2) {
            throw new PHP_BitTorrent_Decoder_Exception('Invalid string. Strings consist of two parts separated by ":".');
        }

        $length = (int) $stringParts[0];
        $lengthLen = strlen($length);

        if (($lengthLen + 1 + $length) > strlen($string)) {
            throw new PHP_BitTorrent_Decoder_Exception('The length of the string does not match the prefix of the encoded data.');
        }

        return substr($string, ($lengthLen + 1), $length);
    }

    /**
     * Decode an encoded PHP array
     *
     * @param string $list
     * @return array
     * @throws PHP_BitTorrent_Decoder_Exception
     */
    static public function decodeList($list) {
        if ($list[0] !== 'l') {
            throw new PHP_BitTorrent_Decoder_Exception('Parameter is not an encoded list.');
        }

        $ret = array();

        $length = strlen($list);
        $i = 1;

        while ($i < $length) {
            if ($list[$i] === 'e') {
                break;
            }

            $part = substr($list, $i);
            $decodedPart = static::decode($part);
            $ret[] = $decodedPart;
            $i += strlen(PHP_BitTorrent_Encoder::encode($decodedPart));
        }

        return $ret;
    }

    /**
     * Decode an encoded PHP associative array
     *
     * @param string $dictionary
     * @return array
     * @throws PHP_BitTorrent_Decoder_Exception
     */
    static public function decodeDictionary($dictionary) {
        if ($dictionary[0] !== 'd') {
            throw new PHP_BitTorrent_Decoder_Exception('Parameter is not an encoded dictionary.');
        }

        $length = strlen($dictionary);
        $ret = array();
        $i = 1;

        while ($i < $length) {
            if ($dictionary[$i] === 'e') {
                break;
            }

            $keyPart = substr($dictionary, $i);
            $key = static::decodeString($keyPart);
            $keyPartLength = strlen(PHP_BitTorrent_Encoder::encodeString($key));

            $valuePart = substr($dictionary, ($i + $keyPartLength));
            $value = static::decode($valuePart);
            $valuePartLength = strlen(PHP_BitTorrent_Encoder::encode($value));

            $ret[$key] = $value;
            $i += ($keyPartLength + $valuePartLength);
        }

        return $ret;
    }
}