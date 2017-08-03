<?php

namespace Rx\React;

class ToFileObserver extends StreamSubject
{
    public function __construct(string $fileName)
    {
        parent::__construct(@fopen($fileName, 'wb'));
    }
}
