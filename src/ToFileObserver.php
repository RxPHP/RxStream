<?php

namespace Rx\React;

use React\EventLoop\LoopInterface;
use React\Stream\WritableResourceStream;
use Rx\ObserverInterface;

class ToFileObserver implements ObserverInterface
{
    private $stream;

    public function __construct(string $fileName, LoopInterface $loop = null)
    {
        $loop         = $loop ?: \EventLoop\getLoop();
        $this->stream = new WritableResourceStream(@fopen($fileName, 'wb'), $loop);
    }

    public function onCompleted()
    {
        $this->stream->end();
    }

    public function onError(\Throwable $error)
    {
        $this->stream->close();
    }

    public function onNext($value)
    {
        $this->stream->write($value);
    }
}
