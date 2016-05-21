<?php

include __DIR__ . "/../../vendor/autoload.php";

$fileSubject = new \Rx\React\StreamSubject(fopen(__DIR__ . "/../test2.csv", 'r+w'));

$fileSubject
    ->take(1)
    ->mapTo("something even cooler")
    ->doOnNext(function ($x) {
        echo "writing '$x' to a file", PHP_EOL;
    })
    ->subscribe(new \Rx\Observer\CallbackObserver(
        [$fileSubject, 'onNext']
    ));
