<?php

include_once 'YmlGenerator.php';

$outputFile = 'bags.xml';
$generator = new YmlGenerator($outputFile, true);
$generator->generate();