<?php
/**
 * PHP BitTorrent
 *
 * Copyright (c) 2011-2012 Christer Edvartsen <cogo@starzinger.net>
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
 * @package Encoder
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */

namespace PHP\BitTorrent;

use InvalidArgumentException;

/**
 * Encode encodable PHP variables to the BitTorrent counterpart
 *
 * @package Encoder
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 */
class Encoder {
    /**
     * Encode any encodable variable
     *
     * @param mixed $var
     * @return string
     * @throws InvalidArgumentException
     */
    public function encode($var) {
        if (is_int($var)) {
            return $this->encodeInteger($var);
        } else if (is_string($var)) {
            return $this->encodeString($var);
        } else if (is_array($var)) {
            $size = count($var);

            for ($i = 0; $i < $size; $i++) {
                if (!isset($var[$i])) {
                    return $this->encodeDictionary($var);
                }
            }

            return $this->encodeList($var);
        }

        throw new InvalidArgumentException('Variables of type ' . gettype($var) . ' can not be encoded.');
    }

    /**
     * Encode an integer
     *
     * @param int $integer
     * @return string
     * @throws InvalidArgumentException
     */
    public function encodeInteger($integer) {
        if (!is_int($integer)) {
            throw new InvalidArgumentException('Expected integer, got: ' . gettype($integer) . '.');
        }

        return 'i' . $integer . 'e';
    }

    /**
     * Encode a string
     *
     * @param string $string
     * @return string
     * @throws InvalidArgumentException
     */
    public function encodeString($string) {
        if (!is_string($string)) {
            throw new InvalidArgumentException('Expected string, got: ' . gettype($string) . '.');
        }

        return strlen($string) . ':' . $string;
    }

    /**
     * Encode a list (regular PHP array)
     *
     * @param array $list
     * @return string
     * @throws InvalidArgumentException
     */
    public function encodeList($list) {
        if (!is_array($list)) {
            throw new InvalidArgumentException('Expected array, got: ' . gettype($list) . '.');
        }

        $ret = 'l';

        foreach ($list as $value) {
            $ret .= $this->encode($value);
        }

        return $ret . 'e';
    }

    /**
     * Encode a dictionary (associative PHP array)
     *
     * @param array $dictionary
     * @return string
     * @throws InvalidArgumentException
     */
    public function encodeDictionary($dictionary) {
        if (!is_array($dictionary)) {
            throw new InvalidArgumentException('Expected array, got: ' . gettype($dictionary) . '.');
        }

        $ret = 'd';

        foreach ($dictionary as $key => $value) {
            $ret .= $this->encodeString((string) $key) . $this->encode($value);
        }

        return $ret . 'e';
    }
}
