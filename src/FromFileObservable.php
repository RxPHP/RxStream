<?php

namespace Rx\React;

use React\Stream\Stream;
use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
use Rx\Operator\CutOperator;
use Rx\Observable;
use Rx\ObserverInterface;


class FromFileObservable extends Observable
{
    /** @var  string */
    private $fileName;

    /** @var  string */
    private $mode;

    public function __construct($fileName, $mode = 'r')
    {
        $this->fileName = $fileName;
        $this->mode     = $mode;
    }

    /**
     * @param ObserverInterface $observer
     * @return \Rx\Disposable\CompositeDisposable|\Rx\DisposableInterface
     */
    public function _subscribe(ObserverInterface $observer): DisposableInterface
    {

        try {
            $stream = new StreamSubject(fopen($this->fileName, $this->mode));

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
