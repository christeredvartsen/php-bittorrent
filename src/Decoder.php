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
 * Decode bittorrent strings to it's PHP variable counterpart
 *
 * @package Decoder
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class Decoder implements DecoderInterface {
    /**
     * Encoder instance
     *
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * Class constructor
     *
     * @param EncoderInterface $encoder An instance of an encoder
     */
    public function __construct(EncoderInterface $encoder = null) {
        if ($encoder === null) {
            $encoder = new Encoder();
        }

        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function decodeInteger($integer) {
        if ($integer[0] !== 'i' || (!$ePos = strpos($integer, 'e'))) {
            throw new InvalidArgumentException('Invalid integer. Integers must start wth "i" and end with "e".');
        }

        $integer = substr($integer, 1, ($ePos - 1));
        $len = strlen($integer);

        if (($integer[0] === '0' && $len > 1) || ($integer[0] === '-' && $integer[1] === '0') || !is_numeric($integer)) {
            throw new InvalidArgumentException('Invalid integer value.');
        }

        if (PHP_INT_SIZE === 8) {
            return (int) $integer;
        }

        return $integer;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
