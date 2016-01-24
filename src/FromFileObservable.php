<?php

namespace Rx\React;

use React\Stream\Stream;
use Rx\Disposable\CallbackDisposable;
use Rx\Extra\Operator\CutOperator;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class FromFileObservable extends Observable
{
    /** @var  string */
    private $fileName;

    /** @var  string */
    private $mode;


    public function __construct($fileName, $mode = "r")
    {
        $this->fileName = $fileName;
        $this->mode     = $mode;
    }

    /**
     * @param ObserverInterface $observer
     * @param SchedulerInterface|null $scheduler
     * @return \Rx\Disposable\CompositeDisposable|\Rx\DisposableInterface
     */
    public function subscribe(ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {

        try {
            $stream = new Stream(fopen($this->fileName, $this->mode), \EventLoop\getLoop());

            $stream->on('data', function ($data) use ($observer) {
                $observer->onNext($data);
            });

            $stream->on('error', function ($error) use ($observer) {
                $e = new \Exception($error);
                $observer->onError($e);
            });

            $stream->on('close', function () use ($observer) {
                $observer->onCompleted();
            });

            return new CallbackDisposable(function () use ($stream) {
                $stream->close();
            });

        } catch (\Exception $e) {
            $observer->onError($e);
        }

    }

    /**
     * Cuts the stream based upon a delimiter.
     *
     * @param string $lineEnd
     * @return Observable\AnonymousObservable
     */
    public function cut($lineEnd = PHP_EOL)
    {
        return $this->lift(function () use ($lineEnd) {
            return new CutOperator($lineEnd);
        });
    }
}
