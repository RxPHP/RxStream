# Stream Component

Provides RxPHP Observables for PHP streams

This library is a wrapper around the [ReactPHP](https://github.com/reactphp/stream) stream library.  It uses the [Voryx event-loop](https://github.com/voryx/event-loop) which behaves like the Javascript event-loop.  ie. You don't need to start it.


## Usage

### From File
```php
    
    $source = new \Rx\React\FromFileObservable("example.csv");
    
    $source
        ->cut() //Cut the stream by PHP_EOL
        ->map('str_getcsv') //Convert csv row to an array
        ->map(function (array $row) {
            //Strip numbers from the first field
            $row[0] = preg_replace('/\d+/u', '', $row[0]);
            return $row;
        })
        ->subscribe(
            function ($data) {
                echo $data[0] . "\n";
            },
            function ($e) {
                echo "error\n";
            },
            function () {
                echo "done\n";
            }
        );
    
```

### Read and Write to File


```PHP

$source = new \Rx\React\FromFileObservable("source.txt");
$dest   = new \Rx\React\ToFileObserver("dest.txt");

$source
    ->cut()
    ->filter(function ($row) {
        return strpos($row, 'foo');
    })
    ->map(function ($row) {
        return $row . 'bar';
    })
    ->subscribe($dest);

```

### Stream - echo example

```PHP

$read  = new \Rx\React\StreamSubject(STDIN);

$read
    ->takeWhile(function ($x) {
        return trim($x) != 15;
    })
    ->subscribe(new \Rx\React\StreamSubject(STDOUT));
    
```    