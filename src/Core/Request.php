<?php

namespace Ddrv\TDS\Core;

/**
 * Class Request
 */
class Request
{

    /**
     * @var array
     */
    protected $parameters = array(
        'method' => null,
        'ip' => null,
        'header' => array(
            'user-agent' => null,
            'host' => 'localhost',
        ),
        'query' => array(),
        'path' => array(),
        'cookie' => array(),
        'token' => array(),
        'uri' => array(
            'raw' => null,
            'parsed' => array(),
        ),
        'body' => array(
            'raw' => null,
            'parsed' => array(),
        ),
    );

    /**
     * @param array $server
     * @param array $get
     * @param string $body
     * @param array $cookies
     * @param array $extends
     */
    public function __construct($server, $get=array(), $body = null, $cookies = array(), $extends = array())
    {
        $this->parameters['method'] = isset($server['REQUEST_METHOD'])?$server['REQUEST_METHOD']:'GET';
        foreach ($server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = mb_strtolower(str_replace('_', '-', substr($key, 5)));
                $this->parameters['header'][$header] = $value;
            }
        }
        $headerCookie = array();
        foreach ($cookies as $cookie => $value) {
            $this->parameters['cookie'][$cookie] = $value;
            $headerCookie[] = $cookie.'='.$value.';';
        }
        if ($headerCookie) {
            $this->parameters['header']['cookie'] = implode(' ', $headerCookie);
        }
        $this->parameters['body']['raw'] = $body;
        if ($body && isset($this->parameters['header']['content-type'])) {
            $type = explode(';', $this->parameters['header']['content-type']);
            switch (trim($type[0])) {
                case 'application/x-www-form-urlencoded':
                    parse_str($body, $this->parameters['body']['parsed']);
                    break;
                case 'application/json':
                    $this->parameters['body']['parsed'] = json_decode($body, 1);
                    break;
            }
        }
        $scheme = 'http'.(empty($server['HTTPS'])?'':'s');
        $port = empty($server['SERVER_PORT'])?(($scheme == 'https')?443:80):$server['SERVER_PORT'];
        $path = isset($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '';
        $path = preg_replace('/(^\/+|\?.*)/ui', '', $path);
        $this->parameters['path'] = explode('/', $path);

        $uri = $scheme.'://'.$this->parameters['header']['host'];
        if (($scheme == 'http' && $port != 80) || ($scheme == 'https' && $port != 443)) {
            $uri .= ':'.$port;
        }
        $uri .= '/'.$path;
        if ($get) {
            $this->parameters['query'] = $get;
            $uri .= '?'.http_build_query($get);
        }
        $this->parameters['uri']['raw'] = $uri;
        $this->parameters['uri']['parsed'] = parse_url($uri);

        foreach ($extends as $key=>$value) {
            if (array_key_exists($key, $this->parameters)) continue;
            $this->parameters[$key] = $value;
        }
    }

    /**
     * @param string $in
     * @param string $position
     * @param string|null $pattern
     * @param string|int $match
     * @return null|string
     */
    public function param($in, $position, $pattern=null, $match=0)
    {
        $value = null;
        $in = (string)$in;
        $position = (string)$position;
        $pattern = (string)$pattern;
        $match = (string)$match;
        $value = $this->$in($position);
        if (!$pattern) {
            return $value;
        }
        preg_match($pattern, $value, $matches);
        return isset($matches[$match])?$matches[$match]:null;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function set($key, $value)
    {
        $key = (string)$key;
        $this->parameters['token'][$key] = $value;
    }

    /**
     * @param bool $assoc
     * @return array
     */
    public function headers($assoc=false)
    {
        if ($assoc) return $this->parameters['header'];
        $headers = array();
        foreach ($this->parameters['header'] as $name=>$value) {
            $headers[] = $name.': '.$value;
        }
        return $headers;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function body($key = '')
    {
        if (!$key) return $this->parameters['body']['raw'];
        $key = (string)$key;
        $keys = explode('.', $key);
        $result = $this->parameters['body']['parsed'];
        foreach ($keys as $key) {
            if (!isset($result[$key])) return null;
            $result = $result[$key];
        }
        return $result;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function uri($key = '')
    {
        if (!$key) return $this->parameters['uri']['raw'];
        $key = (string)$key;
        return isset($this->parameters['uri']['parsed'][$key])?$this->parameters['uri']['parsed'][$key]:null;
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
        $keys = explode('.', $key);
        $result = $this->parameters[$name];
        foreach ($keys as $key) {
            if (!isset($result[$key])) return null;
            $result = $result[$key];
        }
        return $result;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $raw = $this->parameters['method'].' '.$this->uri().' HTTP/1.1'.PHP_EOL;
        foreach ($this->headers() as $header) {
            $raw .= $header.PHP_EOL;
        }
        $raw .= PHP_EOL.$this->body();
        return $raw;
    }
}