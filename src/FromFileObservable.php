<?php

namespace Rx\React;

use React\EventLoop\LoopInterface;
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

    /** @var LoopInterface */
    private $loop;

    public function __construct($fileName, $mode = "r", LoopInterface $loop = null)
    {
        $this->fileName = $fileName;
        $this->mode     = $mode;
        $this->loop     = $loop ?: \EventLoop\getLoop();
    }

    /**
     * @param ObserverInterface $observer
     * @param SchedulerInterface|null $scheduler
     * @return \Rx\Disposable\CompositeDisposable|\Rx\DisposableInterface
     */
    public function subscribe(ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {

        try {
            $stream = new Stream(fopen($this->fileName, $this->mode), $this->loop);

            return (new StreamSubject($stream))->subscribe($observer, $scheduler);

        } catch (\Exception $e) {
            $observer->onError($e);

            return new CallbackDisposable(function () use (&$stream) {
                if ($stream instanceof Stream) {
                    $stream->close();
                }
            });
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
