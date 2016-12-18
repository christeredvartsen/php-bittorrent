<?php
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

    /**
     * {@inheritdoc}
     */
    public function setParam($key, $value) {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function encode($var) {
        if ($this->isInt($var)) {
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

        throw new InvalidArgumentException('Variables of type ' . gettype($var) . ' can not be encoded.');
    }

    /**
     * {@inheritdoc}
     */
    public function encodeInteger($integer) {
        if ($this->isInt($integer)) {
            return 'i' . $integer . 'e';
        }

        throw new InvalidArgumentException('Expected an integer.');
    }

    /**
     * {@inheritdoc}
     */
    public function encodeString($string) {
        if (!is_string($string)) {
            throw new InvalidArgumentException('Expected string, got: ' . gettype($string) . '.');
        }

        return strlen($string) . ':' . $string;
    }

    /**
     * {@inheritdoc}
     */
    public function encodeList(array $list) {
        $ret = 'l';

        foreach ($list as $value) {
            $ret .= $this->encode($value);
        }

        return $ret . 'e';
    }

    /**
     * {@inheritdoc}
     */
    public function encodeDictionary(array $dictionary) {
        ksort($dictionary);

        $ret = 'd';

        foreach ($dictionary as $key => $value) {
            $ret .= $this->encodeString((string) $key) . $this->encode($value);
        }

        return $ret . 'e';
    }

    /**
     * Check if a variable is an integer
     *
     * @param int|string
     * @return boolean
     */
    private function isInt($var) {
        return is_int($var) ||
               (PHP_INT_SIZE === 4 && is_numeric($var) && (strpos($var, '.') === false));
    }
}
