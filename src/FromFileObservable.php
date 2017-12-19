<?php

namespace Rx\React;

use React\EventLoop\LoopInterface;
use React\Stream\ReadableResourceStream;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Operator\CutOperator;
use Rx\Observable;
use Rx\ObserverInterface;

class FromFileObservable extends Observable
{

    private $fileName;

    private $loop;

    public function __construct(string $fileName, LoopInterface $loop = null)
    {
        $this->fileName = $fileName;
        $this->loop     = $loop ?: \EventLoop\getLoop();
    }

    public function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        try {
            $stream = new ReadableResourceStream(@fopen($this->fileName, 'rb'), $this->loop);

            $stream->on('data', function ($data) use ($observer) {
                $observer->onNext($data);
            });

            $stream->on('error', function (\Throwable $e) use ($observer) {
                $observer->onError($e);
            });

            $stream->on('close', function () use ($observer) {
                $observer->onCompleted();
            });

            $stream->on('end', function () use ($observer) {
                $observer->onCompleted();
            });

            return new CallbackDisposable(function () use ($stream) {
                $stream->close();
            });

        } catch (\Throwable $e) {
            $observer->onError($e);
            return new EmptyDisposable();
        }
    }

    /**
     * Cuts the stream based upon a delimiter.
     *
     * @param string $lineEnd
     *
     * @return \Rx\Observable
     */
    public function cut(string $lineEnd = PHP_EOL): Observable
    {
        return $this->lift(function () use ($lineEnd) {
            return new CutOperator($lineEnd);
        });
    }
}
