# Stream Component

Provides RxPHP Observables for PHP streams

This library is a wrapper around the [ReactPHP](https://github.com/reactphp/stream) stream library.  It uses the [Voryx event-loop](https://github.com/voryx/event-loop) which behaves like the Javascript event-loop.  ie. You don't need to start it.


## Usage

### From File
```php
    
    $source = new \Rx\React\FromFileObservable("example.csv");
    
    $source
        ->cut()
        ->map(function ($row) {
            //Convert csv row to an array
            return str_getcsv($row);
        })
        ->map(function (array $row) {
            //Strip numbers from the first field
            $row[0] = preg_replace('/\d+/u', '', $row[0]);
            return $row;
        })
        ->subscribe(new \Rx\Observer\CallbackObserver(
            function ($data) {
                echo $data[0] . "\n";
            },
            function ($e) {
                echo "error\n";
            },
            function () {
                echo "done\n";
            }
        ));
    
```