<?php

namespace App;

class ChildCompany
{
    public $type;

    public $name;

    public $avgGoldBarsPerMonth;

    public function __construct( string $type, string $name = '', float $avgGoldBarsPerMonth = 1)
    {
        $this->type = $type;
        $this->name = $name;
        $this->avgGoldBarsPerMonth = $avgGoldBarsPerMonth;
    }
}
