<?php

include 'vendor/autoload.php';

use MacroMan\DPDLocal;

$all = DPDLocal\Country::loadAll();
var_dump($all);
