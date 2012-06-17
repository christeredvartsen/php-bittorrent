<?php
/**
 * PHP BitTorrent
 *
 * Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
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
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/php-bittorrent
 */

namespace PHP\BitTorrent;

/**
 * Decode bittorrent strings to it's PHP variable counterpart
 *
 * @package Decoder
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/php-bittorrent
 */
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
     * Pr. default the method does not check for these elements.
     *
     * @param string $file Path to the torrent file we want to decode
     * @param boolean $strict If set to true this method will check for certain elements in the
     *                        dictionary.
     * @return array Returns the decoded version of the file as an array
     * @throws \InvalidArgumentException
     */
    function decodeFile($file, $strict = false);

    /**
     * Decode any bittorrent encoded string
     *
     * @param string $string The string to decode
     * @return int|string|array Returns the native PHP counterpart of the encoded string
     * @throws \InvalidArgumentException
     */
    function decode($string);

    /**
     * Decode an encoded PHP integer
     *
     * @param string $integer The integer to decode
     * @return int Returns the decoded integer
     * @throws \InvalidArgumentException
     */
    function decodeInteger($integer);

    /**
     * Decode an encoded PHP string
     *
     * @param string $string The string to decode
     * @return string Returns the decoded string value
     * @throws \InvalidArgumentException
     */
    function decodeString($string);

    /**
     * Decode an encoded PHP array
     *
     * @param string $list Encoded list
     * @return array Returns a numerical array
     * @throws \InvalidArgumentException
     */
    function decodeList($list);

    /**
     * Decode an encoded PHP associative array
     *
     * @param string $dictionary Encoded dictionary
     * @return array Returns an associative array
     * @throws \InvalidArgumentException
     */
    function decodeDictionary($dictionary);
}
