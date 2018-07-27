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
     * @var string
     */
    protected $criteria;

    /**
     * @var array
     */
    protected $tokens = array();

    /**
     * Click constructor.
     * @param $request
     * @param $response
     * @param $link
     * @param $criteria
     * @param $tokens
     */
    public function __construct(Request $request, Response $response, $link, $criteria, $tokens)
    {
        $this->request = $request;
        $this->response = $response;
        $this->link = $link;
        $this->criteria = $criteria;
        $this->tokens = $tokens;
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
     * @return string
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