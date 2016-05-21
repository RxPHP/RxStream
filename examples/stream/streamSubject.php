<?php

include __DIR__ . "/../../vendor/autoload.php";


$stream = new \React\Stream\Stream(fopen(__DIR__ . "/../test2.csv", 'r+w'), \EventLoop\getLoop());

$fileSubject = new \Rx\React\StreamSubject($stream);

$fileSubject
    ->take(1)
    ->mapTo("something even cooler")
    ->doOnNext(function ($x) {
        echo "writing '$x' to a file", PHP_EOL;
    })
    ->subscribe(new \Rx\Observer\CallbackObserver(
        [$fileSubject, 'onNext']
    ));
