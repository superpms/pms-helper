<?php

namespace pms\helper;

class Process
{

    static function strToAction($actionName, $value)
    {
        $actionName = strtoupper($actionName);
        $actionName = str_replace('|', ',', $actionName);
        $actionName = explode(',', $actionName);
        foreach ($actionName as $item) {
            $item = trim($item);
            switch ($item) {
                case 'MD5':
                    $value = md5($value);
                    break;
                case 'STRTOTIME':
                    $value = strtotime($value);
                    break;
                case "INT":
                case "STRTOINT":
                case "NUMBER":
                case "STRTONUMBER":
                case "DOUBLE":
                case "STRTODOUBLE":
                    $value = $value * 1;
                    break;
                case "STR":
                case "STRING":
                    $value = $value . '';
                    break;
                case "TOJSONSTR":
                case "TOJSONSTRING":
                    $value = json_encode($value);
                    break;
                case "JSONSTRTOARRAY":
                case "JSONSTRTOARR":
                case "JSONSTRINGTOARRAY":
                case "JSONSTRINGTOARR":
                    if (is_string($value)) {
                        $value = json_decode($value, true);
                    }
                    break;
            }
        }
        return $value;
    }

    static function strToGlobal($value, $prefix = '@')
    {
        foreach (self::getDefaultValueMap() as $dKey => $dItem) {
            $value = str_replace($prefix . $dKey, $dItem, $value);
        }
        return $value;
    }

    private static function getDefaultValueMap(): array
    {
        return [
            '__NOW_UNIX_TIME10__' => time(),
            '__NOW_UNIX_TIME__' => time(),
        ];
    }


    static function validType($type, $datum): bool
    {
        try {
            $type = strtoupper($type);
            $type = str_replace('||', '|', $type);
            $type = str_replace('|', ',', $type);
            $type = explode(',', $type);
            foreach ($type as $value) {
                $default = true;
                switch ($value) {
                    case 'INT':
                        $default = is_integer($datum);
                        break;
                    case 'DOUBLE':
                        $default = is_double($datum);
                        break;
                    case 'NUMBER':
                        $default = !is_string($datum) && is_numeric($datum);
                        break;
                    case 'STRING':
                    case 'STR':
                        $default = is_string($datum);
                        break;
                    case 'ARRAY':
                    case 'ARR':
                        $default = is_array($datum);
                        break;
                    case 'BOOL':
                    case 'BOOLEAN':
                        $default = is_bool($datum);
                        break;
                    case 'FILE':
                        $default = isset($datum['tmp_name']) && is_file($datum['tmp_name']);
                        break;
                }
                if ($default) {
                    return true;
                }
            }
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    static function realEmpty($datum): bool
    {
        return !is_numeric($datum) && !is_bool($datum) && empty($datum);
    }
}