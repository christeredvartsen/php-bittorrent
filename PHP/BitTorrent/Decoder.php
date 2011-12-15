<?php
/**
 * PHP BitTorrent
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
 * @package Decoder
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */

namespace PHP\BitTorrent;

use InvalidArgumentException;

/**
 * Decode bittorrent strings to it's PHP variable counterpart
 *
 * @package Decoder
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class Decoder {
    /**
     * Encoder instance
     *
     * @var PHP\BitTorrent\Encoder
     */
    private $encoder;

    /**
     * Class constructor
     *
     * @param PHP\BitTorrent\Encoder $encoder An instance of an encoder
     */
    public function __construct(Encoder $encoder = null) {
        if ($encoder === null) {
            $encoder = new Encoder();
        }

        $this->encoder = $encoder;
    }

    /**
     * Decode a file
     *
     * @param string $file Path to the torrent file we want to decode
     * @param boolean $strict If set to true this method will check for certain elements in the dictionary.
     * @return array
     * @throws InvalidArgumentException
     */
    public function decodeFile($file, $strict = false) {
        if (!is_readable($file)) {
            throw new InvalidArgumentException('File ' . $file . ' does not exist or can not be read.');
        }

        $dictionary = $this->decodeDictionary(file_get_contents($file, true));

        if ($strict) {
            if (!isset($dictionary['announce']) || !is_string($dictionary['announce'])) {
                throw new InvalidArgumentException('Missing "announce" key.');
            } else if (!isset($dictionary['info']) || !is_array($dictionary['info'])) {
                throw new InvalidArgumentException('Missing "info" key.');
            }
        }

        return $dictionary;
    }

    /**
     * Decode any bittorrent encoded string
     *
     * @param string $string
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function decode($string) {
        if ($string[0] === 'i') {
            return $this->decodeInteger($string);
        } else if ($string[0] === 'l') {
            return $this->decodeList($string);
        } else if ($string[0] === 'd') {
            return $this->decodeDictionary($string);
        } else if (preg_match('/^\d+:/', $string)) {
            return $this->decodeString($string);
        }

        throw new InvalidArgumentException('Parameter is not correctly encoded.');
    }

    /**
     * Decode an encoded PHP integer
     *
     * @param string $integer
     * @return int
     * @throws InvalidArgumentException
     */
    public function decodeInteger($integer) {
        if ($integer[0] !== 'i' || (!$ePos = strpos($integer, 'e'))) {
            throw new InvalidArgumentException('Invalid integer. Inteers must start wth "i" and end with "e".');
        }

        $int = substr($integer, 1, ($ePos - 1));
        $intLen = strlen($int);

        if (($int[0] === '0' && $intLen > 1) || ($int[0] === '-' && $int[1] === '0') || !is_numeric($int)) {
            throw new InvalidArgumentException('Invalid integer value.');
        }

        return (int) $int;
    }

    /**
     * Decode an encoded PHP string
     *
     * @param string $string
     * @return string
     * @throws InvalidArgumentException
     */
    public function decodeString($string) {
        $stringParts = explode(':', $string, 2);

        // The string must have two parts
        if (count($stringParts) !== 2) {
            throw new InvalidArgumentException('Invalid string. Strings consist of two parts separated by ":".');
        }

        $length = (int) $stringParts[0];
        $lengthLen = strlen($length);

        if (($lengthLen + 1 + $length) > strlen($string)) {
            throw new InvalidArgumentException('The length of the string does not match the prefix of the encoded data.');
        }

        return substr($string, ($lengthLen + 1), $length);
    }

    /**
     * Decode an encoded PHP array
     *
     * @param string $list
     * @return array
     * @throws InvalidArgumentException
     */
    public function decodeList($list) {
        if ($list[0] !== 'l') {
            throw new InvalidArgumentException('Parameter is not an encoded list.');
        }

        $ret = array();

        $length = strlen($list);
        $i = 1;

        while ($i < $length) {
            if ($list[$i] === 'e') {
                break;
            }

            $part = substr($list, $i);
            $decodedPart = $this->decode($part);
            $ret[] = $decodedPart;
            $i += strlen($this->encoder->encode($decodedPart));
        }

        return $ret;
    }

    /**
     * Decode an encoded PHP associative array
     *
     * @param string $dictionary
     * @return array
     * @throws InvalidArgumentException
     */
    public function decodeDictionary($dictionary) {
        if ($dictionary[0] !== 'd') {
            throw new InvalidArgumentException('Parameter is not an encoded dictionary.');
        }

        $length = strlen($dictionary);
        $ret = array();
        $i = 1;

        while ($i < $length) {
            if ($dictionary[$i] === 'e') {
                break;
            }

            $keyPart = substr($dictionary, $i);
            $key = $this->decodeString($keyPart);
            $keyPartLength = strlen($this->encoder->encodeString($key));

            $valuePart = substr($dictionary, ($i + $keyPartLength));
            $value = $this->decode($valuePart);
            $valuePartLength = strlen($this->encoder->encode($value));

            $ret[$key] = $value;
            $i += ($keyPartLength + $valuePartLength);
        }

        return $ret;
    }
}
