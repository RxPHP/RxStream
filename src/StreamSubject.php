<?php

namespace Rx\React;

use React\Stream\Stream;
use Rx\Disposable\BinaryDisposable;
use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
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
     */
    public function __construct($resource)
    {
        $loop = \EventLoop\getLoop();

        $this->stream = new Stream($resource, $loop);
    }

    public function onNext($data)
    {
        if (!$this->stream->isWritable()) {
            throw new \Exception('Stream must be writable');
        }

        $this->stream->write($data);
    }

    public function onCompleted()
    {
        $this->stream->end();

        parent::onCompleted();
    }

    public function _subscribe(ObserverInterface $observer): DisposableInterface
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

        $disposable = parent::_subscribe($observer);

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
        }
    }

    /**
     * @return Stream
     */
    public function getStream()
    {
        return $this->stream;
    }
}
