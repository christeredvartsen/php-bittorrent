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
 * Encode encodable PHP variables to the BitTorrent counterpart
 *
 * @package PHP_BitTorrent
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class PHP_BitTorrent_Encoder {
    /**
     * Encode any encodable variable
     *
     * @param mixed $var
     * @return string
     * @throws PHP_BitTorrent_Encoder_Exception
     */
    static public function encode($var) {
        if (is_int($var)) {
            return static::encodeInteger($var);
        } else if (is_string($var)) {
            return static::encodeString($var);
        } else if (is_array($var)) {
            $size = count($var);

            for ($i = 0; $i < $size; $i++) {
                if (!isset($var[$i])) {
                    return static::encodeDictionary($var);
                }
            }

            return static::encodeList($var);
        }

        throw new PHP_BitTorrent_Encoder_Exception('Variables of type ' . gettype($var) . ' can not be encoded.');
    }

    /**
     * Encode an integer
     *
     * @param int $integer
     * @return string
     * @throws PHP_BitTorrent_Encoder_Exception
     */
    static public function encodeInteger($integer) {
        if (!is_int($integer)) {
            throw new PHP_BitTorrent_Encoder_Exception('Expected integer, got: ' . gettype($integer) . '.');
        }

        return 'i' . $integer . 'e';
    }

    /**
     * Encode a string
     *
     * @param string $string
     * @return string
     * @throws PHP_BitTorrent_Encoder_Exception
     */
    static public function encodeString($string) {
        if (!is_string($string)) {
            throw new PHP_BitTorrent_Encoder_Exception('Expected string, got: ' . gettype($string) . '.');
        }

        return strlen($string) . ':' . $string;
    }

    /**
     * Encode a list (regular PHP array)
     *
     * @param array $list
     * @return string
     * @throws PHP_BitTorrent_Encoder_Exception
     */
    static public function encodeList($list) {
        if (!is_array($list)) {
            throw new PHP_BitTorrent_Encoder_Exception('Expected array, got: ' . gettype($list) . '.');
        }

        $ret = 'l';

        foreach ($list as $value) {
            $ret .= static::encode($value);
        }

        return $ret . 'e';
    }

    /**
     * Encode a dictionary (associative PHP array)
     *
     * @param array $dictionary
     * @return string
     * @throws PHP_BitTorrent_Encoder_Exception
     */
    static public function encodeDictionary($dictionary) {
        if (!is_array($dictionary)) {
            throw new PHP_BitTorrent_Encoder_Exception('Expected array, got: ' . gettype($dictionary) . '.');
        }

        $ret = 'd';

        foreach ($dictionary as $key => $value) {
            $ret .= static::encodeString((string) $key) . static::encode($value);
        }

        return $ret . 'e';
    }
}