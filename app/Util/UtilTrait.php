<?php

namespace App\Util;

trait UtilTrait
{

    /**
     * @param \stdClass $object
     *
     * @return array
     */
    public function objectToArray(\stdClass $object): array
    {
        if (\is_object($object)) {
            $object = \get_object_vars($object);
        }
        if (\is_array($object)) {
            return \array_map(__METHOD__, $object);
        }
        return $object;
    }

    /**
     * @param array $array
     *
     * @return \stdClass
     */
    public function arrayToObject(array $array): \stdClass
    {
        return \json_decode(\json_encode($array));
    }

    /**
     * @param array  $array
     * @param string $string
     * @param string $separator
     *
     * @return string
     * @throws \Exception
     */
    public function arrayToString(array $array, string $string, string $separator = '<br/>'): string
    {
        foreach ($array as $key => $item) {
            if (\is_array($item)) {
                $string .= $key . " => " . self::arrayToString(
                    $item,
                    $string,
                    $separator
                );
            } else {
                $string .= $key . " => " . $item . $separator;
            }
        }
        return $string;
    }

    /**
     * @param array $array
     * @param       $object
     *
     * @return array
     */
    public function createCollection(array $array, $object): array
    {
        if (!\is_array($array)) {
            throw new \InvalidArgumentException("\$array must be an array");
        }
        if (!\class_exists($object, true)) {
            throw new \InvalidArgumentException("\$object must be a valid class");
        }
        $collection = [];
        foreach ($array as $item) {
            $collection[] = new $object($item);
        }
        return $collection;
    }

    /**
     * @param string $data
     *
     * @return bool
     */
    public function isSerialized($data): bool
    {
        if (!\is_string($data)) {
            return false;
        }
        $data = \trim($data);
        if ('N;' == $data) {
            return true;
        }
        $badions = null;
        if (!\preg_match('/^([adObis]):/', $data, $badions)) {
            return false;
        }
        switch ($badions[1]) {
            case 'a':
            case 'O':
            case 's':
                if (\preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                    return true;
                }
                break;
            case 'b':
            case 'i':
            case 'd':
                if (\preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * @param string $file
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function parseCsv($file): array
    {
        if (!is_file($file)) {
            throw new \InvalidArgumentException("$file is not a valid file");
        }
        $result = [];
        $handle = \fopen($file, 'r');
        while (false !== ($data = \fgetcsv($handle, 2048, ','))) {
            \array_push($result, $data);
        }
        return $result;
    }

    /**
     * @param array  $collection
     * @param string $key
     * @param string $value
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function fetchPairs(array $collection, string $key, string $value)
    {
        if (!\is_array($collection)) {
            throw new \InvalidArgumentException('$collection must be an array');
        }
        if (!is_object($collection[0])) {
            throw new \InvalidArgumentException('$collection must be an object collection');
        }
        $keyMethod = 'get' . \ucfirst($key);
        $valueMethod = 'get' . \ucfirst($value);
        if (
            !\method_exists($collection[0], $keyMethod)
            || !\method_exists($collection[0], $keyMethod)
        ) {
            throw new \InvalidArgumentException('$key must be a valid method');
        }
        $result = [];
        foreach ($collection as $item) {
            $result[$item->$keyMethod()] = $item->$valueMethod();
        }
        return $result;
    }

    /**
     * @param array  $arr
     * @param string $col
     * @param int    $dir
     */
    public function arraySortByColumn(
        array &$arr,
        string $col,
        int $dir = SORT_ASC
    ): void {
        $sort_col = [];
        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$col];
        }
        \array_multisort($sort_col, $dir, $arr);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function isJson(string $string): string
    {
        \json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function slugify(string $text): string
    {
        $text = self::normalize($text);
        $text = \preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = \trim($text, '-');
        $text = \iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = \strtolower($text);
        $text = \preg_replace('~[^-\w]+~', '', $text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function normalize(string $string): string
    {
        $table = [
            'Š' => 'S',
            'š' => 's',
            'Đ' => 'Dj',
            'đ' => 'dj',
            'Ž' => 'Z',
            'ž' => 'z',
            'Č' => 'C',
            'č' => 'c',
            'Ć' => 'C',
            'ć' => 'c',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'B',
            'ß' => 'Ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'o',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ý' => 'y',
            'þ' => 'b',
            'ÿ' => 'y',
            'Ŕ' => 'R',
            'ŕ' => 'r',
        ];
        return \strtr($string, $table);
    }
}
