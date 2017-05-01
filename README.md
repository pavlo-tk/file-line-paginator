# File Line Paginator
This library allows you to:
1. Read a file line by line, applying processors to each line and receive structured array as a result for each line.
2. Paginate troughout a file.

Helpful to read and parse log-files.

## Example
Having a line in the file like

`[2016-05-07 17:12:58] php.INFO: Error message. {"type":16384,"file":"some/path/to/file.php","line":3192,"level":4352}`

After reading and applying the `MonologLineFormatterProcessor` that uses Monolog-style pattern for parsing strings, you will receive such result:

```
array:
    0 =>
        'datetime': '2016-05-07 17:12:58'
        'channel' => 'php'
        'level_name' => 'INFO'
        'message' => 'Error message.'
        'context' => array:
              'type'=> 16384
              'level' => 4352
        'file' => 'some/path/to/file.php'
        'line' => 3192
    1 => ...
    2 => ...
```
You can use any pattern for parsing a strings.

#How to use
```php
$filePaginator = new FileLinePaginator('path/to/file.log');
$filePaginator->addProcessor(new MonologLineFormatterProcessor());

$rows = $filePaginator->get($offset, $limit);
```