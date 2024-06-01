<?php

namespace pms\helper;
class Network
{


    private static array $contentTypeMap = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogg',
        'video/mpeg' => 'mpeg',
        'video/quicktime' => 'mov',
        'video/x-msvideo' => 'avi',
        'video/x-ms-wmv' => 'wmv',
        'video/x-flv' => 'flv',
        'video/x-matroska' => 'mkv',
        'video/x-m4v' => 'm4v',
        // 图片
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/bmp' => 'bmp',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'image/tiff' => 'tiff',
        'image/x-icon' => 'ico',

        // 音频
        'audio/mpeg' => 'mp3',
        'audio/ogg' => 'ogg',
    ];

    /**
     * 格式化http报文头
     * @param string $header
     * @return array
     */
    public static function formatHttpHeaderMessage(string $header): array
    {
        if (empty($header)) {
            return [];
        }
        $header = explode("\r\n", $header);
        $header = array_filter($header);
        $header = array_map(function ($item) {
            $item = explode(':', $item);
            if (str_starts_with($item[0], "HTTP/")) {
                return [
                    'key' => 'Http-Type',
                    'value' => $item[0],
                ];
            }
            return [
                'key' => $item[0],
                'value' => $item[1] ? ltrim($item[1], ' ') : '',
            ];
        }, $header);
        // 吧一维数组的每一项都转全小写
        $header = array_map(function ($item) {
            $item['key'] = strtolower($item['key']);
            return $item;
        }, $header);
        return array_combine(array_column($header, 'key'), array_column($header, 'value'));// 键值对
    }

    /**
     * 读取网络文件头信息
     * @param $url
     * @return false|array
     */
    public static function getFileHeader($url): false|array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        if ($data === false) {
            return false;
        }
        $data = static::formatHttpHeaderMessage($data);
        return [
            'size' => $data['content-length'],
            'type' => $data['content-type'],
            'name' => $name ?? $data['content-disposition'] ?? null,
            'ext' => static::$contentTypeMap[$data['content-type']] ?? null,
        ];
    }

    /**
     * 获取网络文件内容
     * @param string $url 网络文件地址
     * @param array $header 请求头
     * @return bool|string
     */
    public static function getFileInfo(string $url, array $header = []): bool|string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($ch);
        if ($data === false) {
            return false;
        }
        return $data;
    }

    /**
     * 下载网络文件到本地
     * @param string $url 网络文件地址
     * @param string $path 本地存储文件夹
     * @param string $name 文件名
     * @param float|int $chunkSize 分块下载大小, 默认4M
     * @return bool
     */
    public static function download(string $url, string $path, string $name, $spareExt = "", float|int $chunkSize = 4 * 1024 * 1024): string|bool
    {
        // 读取文件大小
        $fileInfo = static::getFileHeader($url);
        if ($fileInfo === false) {
            return false;
        }
        $size = $fileInfo['size'];
        $ext = $fileInfo['ext'];
        if (empty($ext)) {
            $ext = $spareExt;
        }
        // 拼接$path和$name 如果$path 的结尾有 / 结尾，或者 $name 的开始有 /,则只保留一个/
        $path = rtrim($path, '/') . '/';
        $name = ltrim($name, '/');
        $filePath = $path . $name . "." . $ext;
        if ($size <= $chunkSize) {
            $data = Network::getFileInfo($url);
            $status = File::createFile($filePath, $data);
            if (!$status) {
                return false;
            }
            return $filePath;
        }
        File::deleteFile($filePath);
        $chunkCount = ceil($size / $chunkSize);
        for ($i = 0; $i < $chunkCount; $i++) {
            $start = $i * $chunkSize;
            $end = ($i + 1) * $chunkSize - 1;
            $data = Network::getFileInfo($url, [
                'Range' => "bytes=$start-$end"
            ]);
            $status = File::createFile($filePath, $data, 'a');
            if (!$status) {
                File::deleteFile($path);
                return false;
            }
        }
        return $filePath;
    }


}