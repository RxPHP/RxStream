<?php

include __DIR__ . "/../../vendor/autoload.php";

$source = new \Rx\React\FromFileObservable(__DIR__ . "/../test.csv");

$source
    ->cut()
    ->subscribe(new \Rx\Observer\CallbackObserver(
        function ($data) {
            echo $data . "\n";
        },
        function ($e) {
            echo "error\n";
        },
        function () {
            echo "done\n";
        }
    ));
