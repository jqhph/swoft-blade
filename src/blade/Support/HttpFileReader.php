<?php

namespace Swoft\Support;

use Psr\Http\Message\ResponseInterface;
use Swoft\App;
use Swoft\Core\RequestContext;

/**
 * http请求静态文件处理类
 *
 * Class HttpFileReader
 * @package Swoft\Support
 */
class HttpFileReader
{
    /**
     * @var string
     */
    protected static $charset = 'utf-8';

    /**
     * 文件类型返回配置
     *
     * @var array
     */
    protected static $mimeTypeMap = [
        'css'  => 'text/css',
        'js'   => 'text/javascript',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'png'  => 'image/png',
        'html' => 'text/html',
        'htm'  => 'text/html',
        'json' => 'application/json'
    ];

    /**
     * 静态资源路径配置
     *
     * @var array
     */
    protected static $assetsPaths = [];

    /**
     * 添加静态资源目录
     * 支持@别名
     *
     * @param string $path
     */
    public static function addAssetsPath(string $path)
    {
        if (!$path) {
            return;
        }
        static::$assetsPaths[] = App::getAlias($path);
    }

    public static function addMimeType(string $extension, string $type)
    {
        if (!$extension || !$type) {
            return;
        }
        static::$mimeTypeMap[$extension] = $type;
    }

    /**
     *
     * @param string $charset
     */
    public static function charset(string $charset)
    {
        self::$charset = $charset;
    }

    /**
     * 读取请求文件
     * 如果文件不存在返回null
     *
     * @return ResponseInterface|null
     */
    public static function read()
    {
        $request = RequestContext::getRequest();
        $path    = $request->getUri()->getPath();

        if ($fullPath = static::findFile($path)) {
            return static::sendFile($fullPath);
        }

        return null;
    }

    /**
     * 发送文件
     *
     * @param string $path 文件完整路径
     * @return ResponseInterface
     */
    public static function sendFile(string $path): ResponseInterface
    {
        $response = RequestContext::getResponse();

        $modifiedAt = static::isNotModified($path);
        if ($modifiedAt === true) {
            return $response->withStatus(304);
        }

        $fileInfo  = pathinfo($path);
        $extension = isset($fileInfo['extension']) ? $fileInfo['extension'] : '';
        $filename  = isset($fileInfo['filename']) ? $fileInfo['filename'] : '';

        $response->withAddedHeader('Content-Type', 'charset='.static::$charset);
        if (!empty(static::$mimeTypeMap[$extension])) {
            $response = $response->withAddedHeader('Content-Type', static::$mimeTypeMap[$extension]);
        } else {
            $response = $response->withAddedHeader('Content-Type', 'application/octet-stream');
            $response = $response->withAddedHeader('Content-Disposition', "attachment; filename=\"$filename\"");
        }
        $response = $response->withAddedHeader('Connection', 'keep-alive');
        if ($modifiedAt) {
            $response = $response->withHeader('Last-Modified', $modifiedAt);
        }

        return $response->withContent(filesystem()->get($path));
    }

    /**
     * 304判断
     *
     * @param string $fullPath
     * @return bool|string
     */
    protected static function isNotModified(string $path)
    {
        $request = RequestContext::getRequest();

        $info          = stat($path);
        $modifiedAt    = $info ? date('D, d M Y H:i:s', $info['mtime']) . ' ' . date_default_timezone_get() : '';
        $modifiedSince = $request->getHeaderLine('if-modified-since');

        if (!empty($modifiedSince) && $info) {
            if (strtolower($modifiedAt) === strtolower($modifiedSince)) {
                return true;
            }
        }

        return $modifiedAt;
    }

    /**
     * 根据请求地址查找文件路径
     *
     * @param string $path
     * @return null|string
     */
    public static function findFile(string $path)
    {
        if (strpos($path, '/') !== 0) {
            $path = '/'.$path;
        }

        foreach (static::$assetsPaths as &$root) {
            $fullPath = $root.$path;

            if (is_file($fullPath)) {
                return $fullPath;
            }
        }

        return null;
    }

}
