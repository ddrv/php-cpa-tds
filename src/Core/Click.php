<?php

namespace Cpa\TDS\Core;

class Click
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var array
     */
    protected $criteria;

    /**
     * @var array
     */
    protected $tokens = array();

    /**
     * @param Request $request
     * @param Response $response
     * @param string $link
     * @param array $criteria
     * @param array $tokens
     */
    public function __construct(Request $request, Response $response, $link, $criteria, $tokens)
    {
        $this->request = $request;
        $this->response = $response;
        $this->link = (string)$link;
        $this->criteria = (array)$criteria;
        $this->tokens = (array)$tokens;
    }

    /**
     * @return Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function link()
    {
        return $this->link;
    }

    /**
     * @return array
     */
    public function criteria()
    {
        return $this->criteria;
    }

    /**
     * @return array
     */
    public function tokens()
    {
        return $this->tokens;
    }
}