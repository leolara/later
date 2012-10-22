<?php

namespace Leolara\Component\Later\Driver;

use Leolara\Component\Later\Invocation;

interface DriverInterface
{
    public function send($method,$arguments,$sid);

    public function checkQueue();

    public function recv();

    public function complete(Invocation $invocation);
}
