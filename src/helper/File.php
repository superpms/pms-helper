<?php

namespace pms\helper;

class File
{

    /**
     * 创建文件夹
     * @param string $path 文件夹地址
     * @param int $permissions 权限, 默认0777
     * @return bool
     */
    static function createFolder(string $path, int $permissions = 0777): bool
    {
        if (file_exists($path)) {
            return false;
        }
        @mkdir($path, $permissions, true);
        return true;
    }

    /**
     * 创建文件
     * @param string $path 文件地址
     * @param string $data 文件数据
     * @param string $mode 文件模式, 默认w，可选w,a,,x,
     * ‘w'：创建文件。如果文件存在则覆盖内容。
     * ‘a'：创建文件。如果文件存在则追加内容。
     * ‘x'：创建文件，在文件不存在时才创建。
     * @return bool
     */
    public static function createFile(string $path, string $data, string $mode = "w"): bool{
        static::createFolder(pathinfo($path)['dirname']);
        $file = fopen($path, $mode);
        if (!$file) {
            return false;
        }
        fwrite($file, $data);
        fclose($file);
        return true;
    }

    /**
     * 删除文件
     * @param string $path
     * @return void
     */
    public static function deleteFile(string $path): void
    {
        @unlink($path);
    }

    /**
     * 分块获取文件md5列表
     * @param string $path 文件路径
     * @param int $chunkSize 分块大小,默认4M
     * @return array
     */
    public static function getFileMd5List(string $path, int $chunkSize = 1024 * 1024 * 4): array{
        if(!is_file($path)){
            return [];
        }
        $file = fopen($path, "r");
        $md5 = [];
        while (!feof($file)) {
            $md5[] = md5(fread($file, $chunkSize));
        }
        fclose($file);
        return $md5;
    }

    /**
     * 分块读取文件
     * @param string $path 文件路径
     * @param \Closure $fn 读取回调
     * @param int $chunkSize 分块大小
     * @return bool
     */
    public static function readFileForChunk(string $path, \Closure $fn,int $chunkSize = 1024 * 1024 * 4): bool{
        if(!is_file($path)){
            return false;
        }
        $file = fopen($path, "r");
        $index = 0;
        while (!feof($file)) {
            $data = fread($file, $chunkSize);
            $fn($data,$index);
            $index++;
        }
        fclose($file);
        return true;
    }

    /**
     * 读取文件
     * @param string $path 文件地址
     * @return bool|string
     */
    public static function readFile(string $path): bool|string{
        $data = file_get_contents($path);
        if (!$data) {
            return false;
        }
        return $data;
    }

    /**
     * 移动文件
     * @param string $move 原文件地址
     * @param string $to 移动后文件地址
     * @return bool
     */
    public static function moveFile(string $move, string $to): bool
    {
        return rename($move, $to);
    }

}