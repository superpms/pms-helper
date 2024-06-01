<?php

namespace pms\helper;

class Hash
{

    public static function md5($str,$salt): string{
        return md5($salt.$str);
    }
    public static function hash256($str,$salt): string
    {
        return hash('sha256',$salt.$str);
    }

    public static function intShifting(int $id,int $factor): string{
        try{
            return base64_encode(strtoupper(dechex($id * $factor)));
        }catch (\Throwable $e){
            return false;
        }
    }

    public static function intUnShifting(string $b64,int $factor): int{
        try{
            return hexdec(base64_decode($b64)) / $factor;
        }catch (\Throwable $e){
            return false;
        }
    }


}