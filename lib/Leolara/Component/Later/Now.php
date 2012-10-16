<?php

namespace Leolara\Component\Later;

use Leolara\Component\Later\Driver\DriverInterface;

class Now
{
    private $driver;
    private $tasksObj;
    private $options = array(
        'loop_max' => 1,
        'empty_queue_wait' => 1000
    );

    private $interrupt = false;

    public function __construct(DriverInterface $driver, $tasksObj, $options=array())
    {
        $this->driver = $driver;
        $this->tasksObj = $tasksObj;
        $this->options = array_merge($this->options,$options);
    }

    public function loop()
    {
        $options = $this->options;

        $executed = 0;
        while( (($options['loop_max'] == 0) || ($options['loop_max'] > $executed)) && !$this->interrupt )
        {
            while (!$this->driver->checkQueue() && !$this->interrupt) {
                usleep($options['empty_queue_wait']);
            }

            $executed += $this->accept();
        }

        echo $executed;

        return $executed;
    }

    public function inter($interrupt = true)
    {
        $this->interrupt = $interrupt;
    }

    private function accept()
    {
        $invocation = $this->driver->recv();

        if (empty($invocation)) {
            return 0;
        }

        call_user_func_array(array($this->tasksObj, $invocation->method), $invocation->arguments);

        $this->driver->complete($invocation);

        return 1;
    }
}
