<?php declare(strict_types=1);
namespace BitTorrent;

use InvalidArgumentException;

class Decoder implements DecoderInterface {
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * Class constructor
     *
     * @param EncoderInterface $encoder Optional encoder instance
     */
    public function __construct(EncoderInterface $encoder = null) {
        $this->encoder = $encoder ?? new Encoder();
    }

    public function decodeFile(string $path, bool $strict = false) : array {
        if (!is_readable($path)) {
            throw new InvalidArgumentException(sprintf('File %s does not exist or can not be read.', $path));
        }

        /** @var string */
        $contents = file_get_contents($path, true);

        return $this->decodeFileContents($contents, $strict);
    }

    public function decodeFileContents(string $contents, bool $strict = false) : array {
        $dictionary = $this->decodeDictionary($contents);

        if ($strict) {
            if (!isset($dictionary['announce']) || !is_string($dictionary['announce']) && !empty($dictionary['announce'])) {
                throw new InvalidArgumentException('Missing or empty "announce" key.');
            } else if (!isset($dictionary['info']) || !is_array($dictionary['info']) && !empty($dictionary['info'])) {
                throw new InvalidArgumentException('Missing or empty "info" key.');
            }
        }

        return $dictionary;
    }

    public function decode(string $string) {
        if ('i' === $string[0]) {
            return $this->decodeInteger($string);
        } else if ('l' === $string[0]) {
            return $this->decodeList($string);
        } else if ('d' === $string[0]) {
            return $this->decodeDictionary($string);
        } else if (preg_match('/^\d+:/', $string)) {
            return $this->decodeString($string);
        }

        throw new InvalidArgumentException('Parameter is not correctly encoded.');
    }

    public function decodeInteger(string $integer) : int {
        if ('i' !== $integer[0] || (!$ePos = strpos($integer, 'e'))) {
            throw new InvalidArgumentException('Invalid integer. Integers must start wth "i" and end with "e".');
        }

        $integer = substr($integer, 1, ($ePos - 1));
        $len = strlen($integer);

        if (('0' === $integer[0] && $len > 1) || ('-' === $integer[0] && '0' === $integer[1]) || !is_numeric($integer)) {
            throw new InvalidArgumentException('Invalid integer value.');
        }

        return (int) $integer;
    }

    public function decodeString(string $string) : string {
        $stringParts = explode(':', $string, 2);

        if (2 !== count($stringParts)) {
            throw new InvalidArgumentException('Invalid string. Strings consist of two parts separated by ":".');
        }

        $length = (int) $stringParts[0];
        $lengthLen = strlen((string) $length);

        if (($lengthLen + 1 + $length) > strlen($string)) {
            throw new InvalidArgumentException('The length of the string does not match the prefix of the encoded data.');
        }

        return substr($string, ($lengthLen + 1), $length);
    }

    public function decodeList(string $list) : array {
        if ('l' !== $list[0]) {
            throw new InvalidArgumentException('Parameter is not an encoded list.');
        }

        $ret = [];

        $length = strlen($list);
        $i = 1;

        while ($i < $length) {
            if ('e' === $list[$i]) {
                break;
            }

            $part = substr($list, $i);
            $decodedPart = $this->decode($part);
            $ret[] = $decodedPart;
            $i += strlen($this->encoder->encode($decodedPart));
        }

        return $ret;
    }

    public function decodeDictionary(string $dictionary) : array {
        if ('d' !== $dictionary[0]) {
            throw new InvalidArgumentException('Parameter is not an encoded dictionary.');
        }

        $length = strlen($dictionary);
        $ret = [];
        $i = 1;

        while ($i < $length) {
            if ('e' === $dictionary[$i]) {
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
