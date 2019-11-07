<?php
$path = __DIR__ . '/data/images';
$directory = new \RecursiveDirectoryIterator($path);
$iterator = new \RecursiveIteratorIterator($directory);
$counter = 0;
/** @var SplFileInfo $info */
foreach ($iterator as $info) {
    if (!is_file($info->getRealPath())) {
        continue;
    }
    $counter++;
    $originalName = $info->getRealPath();
    $newName = $info->getPath() . '/' . $counter . '.' . $info->getExtension();
    rename($originalName, $newName);
    echo $newName . PHP_EOL;
}
