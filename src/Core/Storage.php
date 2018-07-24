<?php

namespace Ddrv\TDS\Core;

use Ddrv\TDS\Core\Storage\Link;
use Ddrv\TDS\Core\Storage\Response;

class Storage
{

    /**
     * @var string
     */
    protected $links;

    /**
     * @var string
     */
    protected $responses;

    /**
     * @var string
     */
    protected $tmp;

    /**
     * @var array
     */
    protected $objects = array(
        'links' => array(),
        'responses' => array(),
    );

    /**
     * Wizard constructor.
     * @param string $links
     * @param string $responses
     * @param null|string $tmp
     */
    public function __construct($links, $responses, $tmp)
    {
        $this->links = $links;
        $this->responses = $responses;
        $this->tmp = $tmp;
    }

    /**
     * @param $key
     * @return Response
     */
    public function response($key)
    {
        if (!isset($this->objects['responses'][$key])) {
            $this->objects['responses'][$key] = new Response($key, $this->responses, $this->tmp);
        }
        return $this->objects['responses'][$key];
    }

    /**
     * @param $key
     * @return Link
     */
    public function link($key)
    {
        if (!isset($this->objects['links'][$key])) {
            $this->objects['links'][$key] = new Link($key, $this->links, $this->tmp);
        }
        return $this->objects['links'][$key];
    }
}