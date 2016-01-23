<?php

include __DIR__ . "/../../vendor/autoload.php";

$source = new \Rx\React\FromFileObservable(__DIR__ . "/../test.csv");

$source
    ->cut()
    ->map(function ($row) {
        return str_getcsv($row);
    })
    ->toArray()
    ->subscribe(new \Rx\Observer\CallbackObserver(
        function (array $array) {
            var_dump($array);
        },
        function ($e) {
            echo "error\n";
        },
        function () {
            echo "done\n";
        }
    ));
