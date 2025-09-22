<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Tero Framework 
 *
 * @link      https://github.com/dromero86/tero
 * @copyright Copyright (c) 2014-2019 Daniel Romero
 * @license   https://github.com/dromero86/tero/blob/master/LICENSE (MIT License)
 */    


class output
{

    const CHARSET       = 'utf-8';
    const MIME_JSON     = 'application/json';
    const MIME_TEXT     = 'text/plain';
    const MIME_HTML     = 'text/html';
    const DEFAULT_HOST  = 'http://localhost/';

    public function json(stdClass|array|Exception $data, $status = 200)
    {
        if ($data instanceof Exception)
            $data = ['result' => false, 'exception' => true, 'code' => $data->getCode(), 'message' => $data->getMessage(), 'file' => $data->getFile(), 'line' => $data->getLine()];
        http_response_code($status);
        header('Content-Type: ' . self::MIME_JSON . '; charset=' . self::CHARSET);
        die(json_encode($data, JSON_PRETTY_PRINT));
    }

    public function text(string $text, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: ' . self::MIME_TEXT . '; charset=' . self::CHARSET);
        die($text);
    }

    public function html(string $text, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: ' . self::MIME_HTML . '; charset=' . self::CHARSET);
        die($text);
    }

    public function write(string $text, array $arg = [])
    {
        printf($text, $arg);
    }

    public function base_url()
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            $base_url = self::DEFAULT_HOST;
        } else {
            $base_url  = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $base_url .= '://' . $_SERVER['HTTP_HOST'];
            $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        }

        return $base_url;
    }

    public function redirect($uri = '', $method = 'location', $http_response_code = 302)
    {
        if (!preg_match('#^https?://#i', $uri)) {
            $uri = $this->base_url() . $uri;
        }

        switch ($method) {
            case 'refresh':
                header("Refresh:0; url={$uri}");
                break;
            default:
                header("Location: {$uri}", TRUE, $http_response_code);
                break;
        }

        exit;
    }
}
