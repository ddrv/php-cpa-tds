<?php

namespace Ddrv\TDS\Config;

class Config
{

    /**
     * @var Key
     */
    public $key;

    /**
     * @var Path
     */
    public $path;

    /**
     * @var TrafficBack
     */
    public $trafficBack;

    /**
     * @param void
     */
    public function __construct()
    {
        $this->key = new Key();
        $this->key->in = 'path';
        $this->key->position = 0;
        $this->path = new Path();
        $basePath = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data';
        $this->path->links = $basePath.DIRECTORY_SEPARATOR.'links';
        $this->path->responses = $basePath.DIRECTORY_SEPARATOR.'responses';
        $this->path->tmp = sys_get_temp_dir();
        $this->trafficBack = new TrafficBack();
    }
}