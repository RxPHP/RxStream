<?php

include __DIR__ . "/../../vendor/autoload.php";

$read = new \Rx\React\StreamSubject(STDIN);

$read
    ->map("trim")
    ->takeWhile(function ($x) {
        return $x != 15;
    })
    ->map(function ($x) {
        return "echo $x \n";
    })
    ->doOnCompleted(function () {
        echo "Thank you for playing echo";
    })
    ->subscribe(new \Rx\React\StreamSubject(STDOUT));
