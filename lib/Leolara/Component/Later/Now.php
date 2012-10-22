<?php

namespace Leolara\Component\Later;

use Leolara\Component\Later\Driver\DriverInterface;

class Now
{
    private $driver;
    private $services;
    private $options = array(
        'loop_max' => 1,
        'empty_queue_wait' => 1000,
        'progressCallback' => null
    );

    private $interrupt = false;
    private $eventhadlers;

    public function __construct(DriverInterface $driver, $services, $options=array())
    {
        $this->driver = $driver;
        $this->services = $services;
        $this->options = array_merge($this->options,$options);
        $this->eventhadlers = array();
    }

    public function loop($options=array())
    {
        $options = array_merge($this->options,$options);

        $executed = 0;
        while( (($options['loop_max'] == 0) || ($options['loop_max'] > $executed)) && !$this->interrupt )
        {
            while (!$this->driver->checkQueue() && !$this->interrupt) {
                usleep($options['empty_queue_wait']);
            }

            $this->trigger('accept:before', $executed);
            $executed += $this->accept();
            $this->trigger('accept:after', $executed);
            if ($options['progressCallback'] instanceof \Closure) {
                $options['progressCallback']($executed);
            }
        }

        return $executed;
    }

    public function inter($interrupt = true)
    {
        $this->interrupt = $interrupt;
    }

    public function on($event,\Closure $closure)
    {
        if (empty($this->eventhadlers[$event])) {
            $this->eventhadlers[$event] = array();
        }

        $this->eventhadlers[$event][] = $closure;
    }

    private function accept()
    {
        $invocation = $this->driver->recv();

        if (empty($invocation)) {
            return 0;
        }

        if (( is_array($this->services) && empty($invocation->sid) ) || ( !is_array($this->services) && !empty($invocation->sid) )) {
            $this->trigger('notfoundservice', $invocation);
            return 0;
        }

        if (is_array($this->services)) {
            $service = $this->services[$invocation->sid];
        } else {
            $service = $this->services;
        }

        $this->trigger('exec:before', $invocation);

        call_user_func_array(array($service, $invocation->method), $invocation->arguments);

        $this->driver->complete($invocation);
        $this->trigger('exec:after', $invocation);

        return 1;
    }

    private function trigger($event,$data)
    {
        if (empty($this->eventhadlers[$event])) {
            return;
        }

        foreach($this->eventhadlers[$event] as $closure) {
            $closure($data);
        }
    }
}
