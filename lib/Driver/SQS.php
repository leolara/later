<?php

namespace Leolara\Component\Later\Driver;

use Leolara\Component\Later\Invocation;

class SQS implements DriverInterface
{
    private $handle;
    private $queue_url;

    public function __construct(\AmazonSQS $handle,$queue_url)
    {
        $this->handle = $handle;
        $this->queue_url = $queue_url;
    }

    public function send($method,$arguments)
    {
        $content = json_encode(array(
            'method' => $method,
            'arguments' => $arguments
        ));

        $response = $this->handle->send_message($this->queue_url,$content);

        return $response->isOK();
    }

    public function checkQueue()
    {
        $response = $this->handle->get_queue_size($this->queue_url);

        if (is_int($response)) {
            return $response;
        }

        return 0;
    }

    public function recv()
    {
        $response = $this->handle->receive_message($this->queue_url);

        if (!$response->isOK()) {
            return null;
        }

        $invocation = new Invocation();

        $content = json_decode( (string) $response->body->ReceiveMessageResult->Message->Body, true);

        $invocation->method = $content['method'];
        $invocation->arguments = $content['arguments'];
        $invocation->driver_handle = (string) $response->body->ReceiveMessageResult->Message->ReceiptHandle;

        return $invocation;
    }

    public function complete(Invocation $invocation)
    {
        $this->handle->delete_message($this->queue_url,$invocation->driver_handle);
    }
}
