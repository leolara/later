<?php

namespace Leolara\Component\Later;

use Leolara\Component\Later\Driver\DriverInterface;

class Later
{
    private $driver;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function __call($name, $arguments)
    {
        $this->driver->send($name,$arguments);
    }
}
