<?php

include_once 'YmlGenerator.php';
include_once 'YmlDocument.php';
include_once 'YmlOffer.php';

$outputFile = 'bags.xml';
$generator = new YmlGenerator($outputFile, true);
$generator->generate();
