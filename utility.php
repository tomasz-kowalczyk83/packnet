<?php

require __DIR__.'/vendor/autoload.php';

use App\GoldBar;
use App\ChildCompany;
use App\ParentCompany;


$company = new ParentCompany;
$company->setup('data.csv');

// task 1
print_r($company->getUplifts());
// task 2
var_dump($company->billProjection());

// task 3
var_dump($company->getTotalBill());
