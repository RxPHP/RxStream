<?php

namespace Rx\React;

use React\EventLoop\LoopInterface;
use React\Stream\Stream;
use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
use Rx\Operator\CutOperator;
use Rx\Observable;
use Rx\ObserverInterface;

class FromFileObservable extends Observable
{
    private $fileName;
    private $mode;
    private $loop;

    public function __construct(string $fileName, string $mode = 'r', LoopInterface $loop = null)
    {
        $this->fileName = $fileName;
        $this->mode     = $mode;
        $this->loop     = $loop ?: \EventLoop\getLoop();
    }

    public function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        try {
            $stream = new StreamSubject(fopen($this->fileName, $this->mode), $this->loop);

            return $stream->subscribe($observer);

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
     */
    public function cut(string $lineEnd = PHP_EOL): Observable
    {
        return $this->lift(function () use ($lineEnd) {
            return new CutOperator($lineEnd);
        });
    }
}
