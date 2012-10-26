# Later README

## What is Later?

A PHP php-the-right-way micro component to run service methods offline transparently and easily, without changing anything most of the time. It is as easy as calling a service method, but it will be run later.

The only requirement is that all methods arguments can be json encode/decode without losing data, and that you can have the service object in the same state offline. This later requirement is usually trivial for utility classes, or what in Symfony 2 are called services.

`Later` integrates specially well with Symfony 2 service container and probably with other service containers.

For example, you might have a object method that resizes photos implemented already. The problem is that the user has to wait for it to execute and the next page will low very slow. You just need to insert `Later` thin layer and the photo resizing will be done offline.

## Architecure

`Later` has two very small components in the architecture

 + Later driver: manage how to add and read jobs from a queue. Currently only Amazon SQS implemented but it is very easy to add new drivers, and we accept Pull Requests!!
 + Later: To invoke a method for later, you just create a Later service object passing the driver, and call the methods on the Later service as if you were calling the original service method.
 + Now: It executes the method invokations offline, just pass the driver and the service object that you want to actually execute the task. You just need a script in the beckground running `Now`.

## Quickstart example

Lets see an example, you have your service to manage photos, that among other things resize photos.

```

namespace Acme;

class PhotoService
{

...

    resizePhoto($photoid)
    {
...
    }

...

}

```

You use this by getting a PhotoSerive object and using it, perhaps using Symfony 2 service container. What we do is to substitute our call to resizePhoto to a call to a Later object.

```

// Before we were resizing the photos online here
// $photoSerive->photoResize($photoid);

use Leolara\Component\Later\Later;
use Leolara\Component\Later\Driver\SQS;

$driver = new SQS($amazon_sqs, $sqs_queue);
$photoServiceLater =  new Later($driver);
$photoServiceLater->photoResize($photoid);

```

`Later` will not run the resize, it will leave it for later.

Now in the background you have a script running `Now`, with the same driver configuration and your `$photoService`.

```
use Leolara\Component\Later\Now;
use Leolara\Component\Later\Driver\SQS;

$driver = new SQS($amazon_sqs, $sqs_queue);
$photoServiceNow =  new Now($driver,$photoService);
$photoServiceNow->loop();
```

`Now::loop` will be executing offline all methods invocations, done online on `Later`, on `$photoService`.

It is that easy.

## Later Drivers

### Amazon SQS

To use this driver we only need to pass two parameters to the constructor.

 + An AmazonSQS object from the official Amazon PHP SDK.
 + The Amazon queue url, you should use different queues for different services. Never use the same queue for two services as the `Now` might try to execute invocations of one service on the other.

### Other drivers

No other drivers implemented at the moment.

## `Later` class documentation

To use `Later` is really simple. Just pass to the constructor a driver instance and you are ready to start calling methods on 'Later' that will be executed later by `Now` on your service.

Nothing else here

## `Now` class documentation

### `Now` constructor

Pass to `Now` constructor the driver configured as in `Later` instatioation and your service as second argument.

   $now = new Now($driver,$service);

You can pass options as a hash in a optional third argument to the constructor.

### `Now` construtor options

 + loop_max: Number of invocations to execute before exiting `Now`. If the value is 0, will execute forever, unless interrupted.
 ++ DEFAULT: 1, will execute one invocation and exit.
 + empty_queue_wait: time in miliseconds, that will way before checking for new peding invocations after no peding invocations are detected.
 ++ DEFAULT: 1000

### Handling offline excutions

To start executing invocations offline just call `loop` on the `Now` object. This method has no arguments.

### Interrupting `Now`

TODO

## Symfony 2 service container integration

TODO

# LICENSE

Copyright (c) 2004-2012 Leo Lara

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

