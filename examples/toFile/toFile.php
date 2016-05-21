<?php

include __DIR__ . "/../../vendor/autoload.php";


$toFile = new \Rx\React\ToFileObserver(__DIR__ . "/../test2.csv");

\Rx\Observable::range(1, 20)
    ->take(5)
    ->mapTo("This is pretty cool \n")
    ->subscribe($toFile);
