<?php

namespace Leolara\Component\Later;

use Leolara\Component\Later\Driver\DriverInterface;

class Later
{
    private $driver;
    private $sid;

    public function __construct(DriverInterface $driver,$sid='')
    {
        $this->driver = $driver;
        $this->sid = $sid;
    }

    public function __call($name, $arguments)
    {
        $this->driver->send($name,$arguments,$this->sid);
    }
}
