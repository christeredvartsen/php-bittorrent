<?php declare(strict_types=1);
namespace BitTorrent;

use InvalidArgumentException;

class Encoder implements EncoderInterface {
    /**
     * Parameters for the encoder
     *
     * @var array
     */
    private $params = [
        // Set to true to encode empty arrays as dictionaries ("de") instead of lists ("le")
        'encodeEmptyArrayAsDictionary' => false,
    ];

    /**
     * Class constructor
     *
     * @param array $params Parameters for the encoder
     */
    public function __construct(array $params = []) {
        $this->params = array_replace($this->params, $params);
    }

    public function encode($var) : string {
        if (is_int($var)) {
            return $this->encodeInteger($var);
        } else if (is_string($var)) {
            return $this->encodeString($var);
        } else if (is_array($var)) {
            $size = count($var);

            if (!$size && $this->params['encodeEmptyArrayAsDictionary']) {
                return $this->encodeDictionary($var);
            }

            for ($i = 0; $i < $size; $i++) {
                if (!isset($var[$i])) {
                    return $this->encodeDictionary($var);
                }
            }

            return $this->encodeList($var);
        }

        throw new InvalidArgumentException(sprintf('Variables of type %s can not be encoded.', gettype($var)));
    }

    public function encodeInteger(int $integer) : string {
        return sprintf('i%de', $integer);
    }

    public function encodeString(string $string) : string {
        return sprintf('%d:%s', strlen($string), $string);
    }

    public function encodeList(array $list) : string {
        $encodedList = array_map(function($value) : string {
            return $this->encode($value);
        }, $list);

        return sprintf('l%se', implode('', $encodedList));
    }

    public function encodeDictionary(array $dictionary) : string {
        ksort($dictionary);

        $encodedDictionary = '';

        foreach ($dictionary as $key => $value) {
            $encodedDictionary .= $this->encodeString((string) $key) . $this->encode($value);
        }

        return sprintf('d%se', $encodedDictionary);
    }
}
