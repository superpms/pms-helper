<?php

namespace pms\helper;

use Exception;

class HttpClient
{

    /**
     * CURL 连接句柄
     * @var resource
     */
    private $handler;

    private array $options = array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => false,
        CURLOPT_FOLLOWLOCATION => 1,
        // 支持gzip
        CURLOPT_ENCODING => '',
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36',
        CURLOPT_AUTOREFERER => true,
        CURLOPT_CONNECTTIMEOUT => 120,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
    );
    /**
     * 原始 Response
     * @var string
     */
    private mixed $responseMessage;

    /**
     * 响应结果
     * @var array
     */


    private array $responseHeader = [];
    private mixed $responseBody;
    private array $responseCookie;


    public function __construct()
    {
        $this->handler = curl_init();
    }

    public function __destruct()
    {
        curl_close($this->handler);
    }

    public function setOptions(array $options): HttpClient
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * 设置请求地址
     * @param string $uri
     * @return HttpClient
     */
    public function setUrl(string $uri): HttpClient
    {
        $this->options[CURLOPT_URL] = $uri;
        return $this;
    }

    public function setMethodAndData(string $method, $data = [] || ""): HttpClient
    {
        $method = strtoupper($method);
        $this->options[CURLOPT_CUSTOMREQUEST] = $method;
        switch ($method) {
            case "GET":
                if (is_array($data) || is_object($data)) {
                    $params = http_build_query($data);
                } else {
                    $params = $data;
                }
                $url = $this->options[CURLOPT_URL];
                if (!str_contains($url, "?")) {
                    $this->options[CURLOPT_URL] = $url . "?" . $params;
                } else {
                    $this->options[CURLOPT_URL] = $url . "&" . $params;
                }
                break;
            case "POST":
                $this->options[CURLOPT_POST] = 1;
                $this->options[CURLOPT_POSTFIELDS] = $data;
                break;
        }
        return $this;
    }

    /**
     * 设置请求头
     * @param array $header
     * @return HttpClient
     */
    public function setHeader(array $header): HttpClient
    {
        $this->options[CURLOPT_HTTPHEADER] = $header;
        return $this;
    }

    /**
     * 设置Cookie
     * @param string $cookie
     * @return HttpClient
     */
    public function setCookie(string $cookie): HttpClient
    {
        $this->options[CURLOPT_COOKIE] = $cookie;
        return $this;
    }


    /**
     * 设置超时时间
     * @param int $time_out
     * @return HttpClient
     */
    public function setTimeOut(int $time_out): HttpClient
    {
        $this->options[CURLOPT_TIMEOUT] = $time_out;
        return $this;
    }

    /**
     * 设置在HTTP请求头中"Referer: "的内容。
     * @param string $referer
     * @return HttpClient
     */
    public function setReferer(string $referer): HttpClient
    {
        $this->options[CURLOPT_REFERER] = $referer;
        return $this;
    }

    /**
     * @param string $user_agent
     * @return HttpClient
     */
    public function setUserAgent(string $user_agent): HttpClient
    {
        $this->options[CURLOPT_USERAGENT] = $user_agent;
        return $this;
    }

    /**
     * 不输出 BODY 部分。同时 Mehtod 变成了 HEAD。修改为 false 时不会变成 GET。
     * @param bool $isGet
     * @return HttpClient
     */
    public function isGetBody(bool $isGet = true): HttpClient
    {
        $this->options[CURLOPT_NOBODY] = !$isGet; // 不输出 BODY 部分
        return $this;
    }

    /**
     * 启用时会将头文件的信息作为数据流输出。
     * @param bool $isGet
     * @return HttpClient
     */
    public function isGetHeader(bool $isGet = false): HttpClient
    {
        $this->options[CURLOPT_HEADER] = $isGet;
        return $this;
    }

    /**
     * 执行 CURL 会话
     * @return HttpClient
     * @throws Exception
     */
    public function exec(): HttpClient
    {
        curl_setopt_array($this->handler, $this->options);
        if (curl_errno($this->handler)) {
            throw new Exception(curl_error($this->handler), curl_errno($this->handler));
        }
        $this->responseMessage = curl_exec($this->handler);
        return $this;
    }

    /**
     * 获取最后跳转地址
     * @param int $opt CURLINFO_EFFECTIVE_URL - 最后一个有效的URL地址
     *                 CURLINFO_HTTP_CODE - 最后一个收到的HTTP代码
     *                 CURLINFO_FILETIME - 远程获取文档的时间，如果无法获取，则返回值为“-1”
     *                 CURLINFO_TOTAL_TIME - 最后一次传输所消耗的时间
     *                 CURLINFO_NAMELOOKUP_TIME - 名称解析所消耗的时间
     *                 CURLINFO_CONNECT_TIME - 建立连接所消耗的时间
     *                 CURLINFO_PRETRANSFER_TIME - 从建立连接到准备传输所使用的时间
     *                 CURLINFO_STARTTRANSFER_TIME - 从建立连接到传输开始所使用的时间
     *                 CURLINFO_REDIRECT_TIME - 在事务传输开始前重定向所使用的时间
     *                 CURLINFO_SIZE_UPLOAD - 以字节为单位返回上传数据量的总值
     *                 CURLINFO_SIZE_DOWNLOAD - 以字节为单位返回下载数据量的总值
     *                 CURLINFO_SPEED_DOWNLOAD - 平均下载速度
     *                 CURLINFO_SPEED_UPLOAD - 平均上传速度
     *                 CURLINFO_HEADER_SIZE - header部分的大小
     *                 CURLINFO_HEADER_OUT - 发送请求的字符串
     *                 CURLINFO_REQUEST_SIZE - 在HTTP请求中有问题的请求的大小
     *                 CURLINFO_SSL_VERIFYRESULT - 通过设置CURLOPT_SSL_VERIFYPEER返回的SSL证书验证请求的结果
     *                 CURLINFO_CONTENT_LENGTH_DOWNLOAD - 从Content-Length: field中读取的下载内容长度
     *                 CURLINFO_CONTENT_LENGTH_UPLOAD - 上传内容大小的说明
     *                 CURLINFO_CONTENT_TYPE - 下载内容的Content-Type:值，NULL表示服务器没有发送有效的Content-Type: header
     * @return HttpClient
     */
    public function setInfo(int $opt = CURLINFO_EFFECTIVE_URL): HttpClient
    {
        $this->result['info'] = curl_getinfo($this->handler, $opt);
        return $this;
    }

    /**
     * 发送请求
     * @return HttpClient
     * @throws Exception
     */
    public function request(): HttpClient{
        $this->exec();
        if (isset($this->options[CURLOPT_HEADER]) && $this->options[CURLOPT_HEADER]) {
            list($header, $body, $cookie) = $this->formatMessage($this->responseMessage);
            $this->responseHeader = $header;
            $this->responseCookie = $cookie;
            $this->responseBody = $body;
        } else {
            $this->responseBody = $this->responseMessage;
        }
        return $this;
    }

    /**
     * 转换返回的body中的字符编码
     * @param string $charset 需要转换的字符编码
     * @return HttpClient
     */
    public function charsetConverter(string $charset = "UTF-8"): HttpClient{
        if (!empty($this->responseBody)) {
            $encode = mb_detect_encoding($this->responseBody, array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5"));
            if ($charset !== $encode) {
                $this->responseBody = mb_convert_encoding($this->responseBody, $charset, $encode);
            }
        }
        return $this;
    }

    /**
     * 处理返回的 body 进行 json 格式化
     * @param bool $tooArray
     * @return HttpClient
     */
    public function jsonToObjectConverter(bool $tooArray = false): HttpClient{
        if (!empty($this->responseBody)) {
            $this->responseBody = json_decode($this->responseBody, $tooArray);
        }
        return $this;
    }


    /**
     * 格式化返回的 HTTP 报文
     * @param string $message HTTP报文
     * @return array [header,body,cookie,redirect]
     */
    private function formatMessage(string $message): array{
        $result_arr = explode("\r\n\r\n", $message);
        $header = $result_arr[count($result_arr) - 2];
        $body = $result_arr[count($result_arr) - 1];
        if (count($result_arr) > 2) {
            $header = $message;
        }
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $cookie = $matches[1];
        $header_str = explode("\r\n", $header);
        $header_array = [];
        foreach ($header_str as $value) {
            if (!str_contains($value, 'HTTP/')) {
                $temp = explode(": ", $value);
                if (count($temp) === 2) {
                    $header_array[$temp[0]] = $temp[1];
                }
            }
        }
        $header = json_encode($header_array);
        return [$header, $body, $cookie];
    }

    /**
     * @return array
     */
    public function getResponseBody(): mixed
    {
        return $this->responseBody;
    }

    /**
     * @return array
     */
    public function getResponseCookie(): array
    {
        return $this->responseCookie;
    }

    /**
     * @return array
     */
    public function getResponseHeader(): array
    {
        return $this->responseHeader;
    }

    /**
     * @return mixed
     */
    public function getResponseMessage(): mixed
    {
        return $this->responseMessage;
    }


}