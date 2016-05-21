<?php

namespace Rx\React;

use React\Stream\Stream;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

class StreamSubject extends Subject
{

    /** @var \React\Stream\Stream */
    private $stream;

    public function __construct(Stream $stream)
    {
        $this->stream = $stream;
    }

    public function onNext($data)
    {

        if (!$this->stream->isWritable()) {
            throw new \Exception('Stream must be writable');
        }

        $this->stream->write($data);
        
        parent::onNext($data);

    }

    public function onCompleted()
    {
        $this->stream->close();
        
        parent::onCompleted();
    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $disposable = parent::subscribe($observer, $scheduler);

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

        return $disposable;
    }

    public function dispose()
    {
        parent::dispose();

        $this->stream->close();
    }

    /**
     * @return Stream
     */
    public function getStream()
    {
        return $this->stream;
    }
}
