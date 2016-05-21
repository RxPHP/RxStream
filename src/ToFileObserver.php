<?php

namespace Rx\React;

use React\EventLoop\LoopInterface;
use React\Stream\Stream;
use Rx\Observable;

class ToFileObserver extends StreamSubject
{

    /**
     * ToFileObserver constructor.
     *
     * @param string $fileName
     * @param LoopInterface|null $loop
     */
    public function __construct($fileName, LoopInterface $loop = null)
    {

        $loop = $loop ?: \EventLoop\getLoop();

        parent::__construct(fopen($fileName, 'w'), $loop);
    }
}
