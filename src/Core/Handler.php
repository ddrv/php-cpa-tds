<?php

namespace Ddrv\TDS\Core;

use Ddrv\TDS\Core\Handler\Result;

class Handler
{
    /**
     * @var string[]
     */
    protected $responses = array();

    /**
     * @var array
     */
    protected $tokens = array();

    /**
     * @var Response[]
     */
    protected $responseObjects = array();

    /**
     * @param string $responsesDirectory
     */
    public function __construct($responsesDirectory)
    {
        foreach ($this->responses as $key) {
            $file = $responsesDirectory.DIRECTORY_SEPARATOR.'response-'.$key.'.php';
            $class = '\Ddrv\TDS\Binary\Response\Response'.mb_strtoupper(md5($key));
            if (file_exists($file)) require_once($file);
            if (class_exists($class)) $this->responseObjects[$key] = new $class();
        }
    }

    /**
     * @param Request $request
     * @return Result
     */
    public function click(Request $request)
    {
        return new Result(null, null, array());
    }

    /**
     * @param string[] $responses
     * @return Response|null
     */
    protected function getResponse($responses)
    {
        $result = array();
        foreach ($responses as $response) {
            if (isset($this->responseObjects[$response])) $result[] = $response;
        }
        if (!$result) return null;
        return $this->responseObjects[$result[array_rand($result)]];
    }
}