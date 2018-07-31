<?php

namespace Cpa\TDS\Core;

use DateTime;
use DateTimeZone;

class Response
{

    /**
     * @var string
     */
    protected $key;

    /**
     * @var int
     */
    protected $status = 404;

    /**
     * @var array
     */
    protected $headers = array('content-type: text/plain');

    /**
     * @var string
     */
    protected $body = 'Not Found';

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @var array
     */
    protected $cookies = array();

    /**
     * @param string $key
     * @param int $status
     * @param array $headers
     * @param string $body
     * @param array $extends
     * @param array $cookies
     */
    public function __construct($key=null, $status = null, $headers = null, $body = null, $extends = null, $cookies = null)
    {
        if (!is_null($key)) $this->key = (string)$key;
        if (!is_null($status)) $this->status = (int)$status;
        if (!is_null($headers)) $this->headers = (array)$headers;
        if (!is_null($body)) $this->body = (string)$body;
        if (!is_null($extends)) $this->parameters = (array)$extends;
        if (!is_null($cookies)) $this->cookies = (array)$cookies;
        $this->setCookies($this->cookies);
    }

    /**
     * @return string
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @return int
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * @param bool $assoc
     * @return array
     */
    public function headers($assoc=false)
    {
        if (!$assoc) return $this->headers;
        $headers = array();
        foreach ($this->headers as $header) {
            $h = explode(':', $header);
            $name = trim(array_shift($h));
            $value = trim(implode(':', $h));
            $headers[$name] = $value;
        }
        return $headers;
    }

    /**
     * @return string
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * @param array $tokens
     * @param bool $clearMasks
     */
    public function replace($tokens, $clearMasks = false)
    {
        foreach ($tokens as $token=>$value) {
            $this->body = str_replace('{{'.$token.'}}', $value, $this->body);
            foreach ($this->headers as &$header) {
                $header = str_replace('{{'.$token.'}}', $value, $header);
                unset($header);
            }
        }
        if ($clearMasks) {
            $this->body = preg_replace('/(\{\{(:)?[a-z0-9\-\._]+\}\})/ui', '', $this->body);
            foreach ($this->headers as &$header) {
                $header = preg_replace('/(\{\{(:)?[a-z0-9\-\._]+\}\})/ui', '', $header);
                unset($header);
            }
        }
    }

    /**
     * @return void
     */
    public function out()
    {
        $message = $this->getMessage($this->status);
        header('Status: '.$this->status.' '.$message);
        foreach ($this->headers as $header) {
            header(trim($header), false);
        }
        echo $this->body;
        die;
    }

    public function setCookies($cookies)
    {
        foreach ($cookies as $key=>$cookie) {
            if (empty($cookie['value'])) continue;
            $header = 'set-cookie: '.(string)$key.'='.(string)$cookie['value'].';';
            if (isset($cookie['domain'])) {
                $header .= ' Domain='.$cookie['domain'].';';
            }
            if (isset($cookie['path'])) {
                $header .= ' Path='.$cookie['path'].';';
            }
            if (!empty($cookie['hours'])) {
                $date = new DateTime(null, new DateTimeZone('GMT'));
                $date = $date->modify('+'.$cookie['hours'].' hours');
                $header .= ' Expires='.$date->format(DATE_RFC7231).';';
            }
            if (!empty($cookie['secure'])) {
                $header .= ' Secure;';
            }
            if (!empty($cookie['httpOnly'])) {
                $header .= ' HttpOnly;';
            }
            $this->headers[] = $header;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $raw = 'HTTP/1.1 '.$this->status.' '.$this->getMessage($this->status).PHP_EOL;
        $raw .= implode(PHP_EOL, $this->headers).PHP_EOL.PHP_EOL;
        $raw .= $this->body;
        return $raw;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!array_key_exists($name, $this->parameters)) return null;
        $key = (string)($arguments?array_shift($arguments):'');
        if ($key == '') return $this->parameters[$name];
        $keys = explode('.', $key);
        $result = $this->parameters[$name];
        foreach ($keys as $key) {
            if (!isset($result[$key])) return null;
            $result = $result[$key];
        }
        return $result;
    }

    /**
     * @param $status
     * @return string
     */
    protected function getMessage($status)
    {
        $messages = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            208 => 'Already Reported',
            226 => 'IM Used',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I am a teapot',
            421 => 'Misdirected Request',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            449 => 'Retry With',
            451 => 'Unavailable For Legal Reasons',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
            520 => 'Unknown Error',
            521 => 'Web Server Is Down',
            522 => 'Connection Timed Out',
            523 => 'Origin Is Unreachable',
            524 => 'A Timeout Occurred',
            525 => 'SSL Handshake Failed',
            526 => 'Invalid SSL Certificate',
        );
        return isset($messages[$status])?$messages[$status]:'';
    }
}
