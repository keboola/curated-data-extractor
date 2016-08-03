<?php

echo "Extractor starting...";

require __DIR__ . "/../vendor/autoload.php";

$main = new Keboola\CuratedDataExtractor\Executor();
try {
    $main->run();
} catch (\Keboola\CuratedDataExtractor\Exception\UserException $e) {
    echo $e->getMessage();
    exit(1);
} catch (\Throwable $e) {
    echo $e->getMessage();
    exit(2);
}
