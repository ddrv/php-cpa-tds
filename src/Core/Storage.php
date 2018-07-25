<?php

namespace Ddrv\TDS\Core;

use Ddrv\TDS\Core\Storage\Link;
use Ddrv\TDS\Core\Storage\Response;

class Storage
{

    /**
     * @var Link
     */
    protected $link;

    /**
     * @var Response
     */
    protected $response;

    /**
     * Wizard constructor.
     * @param string $links
     * @param string $responses
     * @param null|string $tmp
     */
    public function __construct($links, $responses, $tmp)
    {
        $this->response = new Response($responses, $tmp);
        $this->link = new Link($links, $tmp);
    }

    /**
     * @param void
     * @return Response
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * @param void
     * @return Link
     */
    public function link()
    {
        return $this->link;
    }
}