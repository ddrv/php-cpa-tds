<?php

namespace Cpa\TDS\Config;

class TrafficBack
{

    /**
     * @var int
     */
    public $status = 404;

    /**
     * @var string[]
     */
    public $headers = array(
        'content-type' => 'text/plain',
    );

    /**
     * @var string
     */
    public $body = 'Not Found';
}