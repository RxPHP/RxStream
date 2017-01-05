<?php

namespace Rx\React;

class ToFileObserver extends StreamSubject
{

    /**
     * ToFileObserver constructor.
     *
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        parent::__construct(fopen($fileName, 'wb'));
    }
}
