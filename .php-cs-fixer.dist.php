<?php

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

$directories = [];
$finder = Finder::create()
    ->in(__DIR__)
    ->exclude($directories);

return (new Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setFinder($finder);
