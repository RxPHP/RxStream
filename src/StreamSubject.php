<?php

namespace Rx\React;

use React\EventLoop\LoopInterface;
use React\Stream\Stream;
use Rx\Disposable\BinaryDisposable;
use Rx\Disposable\CallbackDisposable;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

class StreamSubject extends Subject
{

    /** @var \React\Stream\Stream */
    private $stream;

    /**
     * StreamSubject constructor.
     *
     * @param resource $resource
     * @param LoopInterface|null $loop
     */
    public function __construct($resource, LoopInterface $loop = null)
    {

        $loop = $loop ?: \EventLoop\getLoop();

        $this->stream = new Stream($resource, $loop);

    }


    public function onNext($data)
    {

        if (!$this->stream->isWritable()) {
            throw new \Exception('Stream must be writable');
        }

        $this->stream->write($data);

        //this will probably get stuck in a loop, not sure if I need it or not
        parent::onNext($data);

    }

    public function onCompleted()
    {

        $this->stream->end();

        parent::onCompleted();
    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {

        $this->stream->on('data', function ($data) use ($observer) {
            $observer->onNext($data);
        });

        $this->stream->on('error', function ($error) use ($observer) {
            $ex = $error instanceof \Exception ? $error : new \Exception($error);
            $observer->onError($ex);
        });

        $this->stream->on('close', function () use ($observer) {
            $observer->onCompleted();
        });

        $disposable = parent::subscribe($observer, $scheduler);

        return new BinaryDisposable($disposable, new CallbackDisposable(function () use ($observer) {
            $this->removeObserver($observer);
            $this->dispose();
        }));
    }

    public function dispose()
    {

        if (!$this->hasObservers()) {
            parent::dispose();
            $this->stream->end();
        };

    }

    /**
     * @return Stream
     */
    public function getStream()
    {
        return $this->stream;
    }
}
