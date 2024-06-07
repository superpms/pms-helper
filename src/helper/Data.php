<?php

namespace pms\helper;


class Data
{

    public static function jsonCompress(array $jsonArray, \Closure $itemClosure = null): array|false
    {
        try {
            $k = "";
            $header = [];
            $body = [];
            foreach ($jsonArray as $key => $item) {
                $current = join("|", array_keys($item));
                if ($key > 0 && $current !== $k) {
                    return false;
                }
                $k = $current;
                $header = [];
                $datum = [];
                if ($itemClosure !== null) {
                    $item = $itemClosure($item);
                }
                foreach ($item as $index => $value) {
                    $header[] = $index;
                    $datum[] = $value;
                }
                $body[] = $datum;
            }
            return [
                'header' => $header,
                'body' => $body
            ];
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function jsonUnCompress(array $jsonArray, \Closure $filter = null): array|false
    {
        try {
            $header = $jsonArray['header'];
            $body = $jsonArray['body'];
            $newArray = [];
            foreach ($body as $datum) {
                $item = [];
                foreach ($header as $index => $key) {
                    $item[$key] = $datum[$index];
                }
                if ($filter !== null) {
                    $item = $filter($item);
                }
                if ($item) {
                    $newArray[] = $item;
                }
            }
            return $newArray;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function strEncryption(string $string, string $key): string
    {
        $len = strlen($key);
        $code = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $k = $i % $len;
            $code .= $string[$i] ^ $key[$k];
        }
        return $code;
    }

    public static function StrToBin($str): string
    {
        //1.列出每个字符
        $arr = preg_split('/(?<!^)(?!$)/u', $str);

        //2.unpack字符
        foreach ($arr as &$v) {
            $temp = unpack('H*', $v);
            $v = base_convert($temp[1], 16, 2);
            unset($temp);

        }
        return join(' ', $arr);
    }

    // 拼接二进制
    public static function boldsJoin(...$args): string
    {
        return join('', $args);
    }

    // 取链上数据
    public static function getChainData(array $data, string $chain, string $chainLevelStr = '.')
    {
        $tmp = $data;
        $fA = explode($chainLevelStr, $chain);
        for ($i = 0; $i < count($fA); $i++) {
            $key = $fA[$i];
            if (isset($tmp[$key])) {
                $tmp = $tmp[$key];
            } else {
                return null;
            }
        }
        return $tmp;
    }

    public static function arrayToXml(array|object $array, string $root = 'root'): bool|string{
        function arrayToXml($array, &$xml): void{
            foreach ($array as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    if (!is_numeric($key)) {
                        $subnode = $xml->addChild($key);
                        arrayToXml($value, $subnode);
                    } else {
                        arrayToXml($value, $xml);
                    }
                } else {
                    $xml->addChild($key, $value);
                }
            }
        }

        $xml = new \SimpleXMLElement('<' . $root . '/>');
        arrayToXml($array, $xml);
        return $xml->saveXML();
    }

    // 获取(按位或)最终结果

    /**
     * @param array $keyMap 或运算的值
     * @param int $value 值
     * @return array
     */
    public static function getBitOr(array$keyMap,int $value):array{
        $result = [];
        foreach ($keyMap as $v) {
            if(($value & $v) === $v){
                $result[] = $v;
            }
        }
        return $result;
    }
}