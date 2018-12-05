<?php

namespace App;

use App\Company;
use App\ChildCompany;

class ParentCompany
{
    protected $basePrice = 0;

    public $childCompanies = [];

    private $uplifts = [];

    /**
     * Reads a csv file and sets up the company according to the data in that file.
     * @param  string  $csvPath        path to a valid csv file
     * @param  integer $numOfCompanies number of child companies to create (default 5)
     * @return
     */
    public function setup(string $csvPath, int $numOfCompanies = 5)
    {
        //check if path to csv is valid
        if (($handle = fopen($csvPath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $csv[] = $data;
            }
            fclose($handle);

            //set the base price for the gold bar
            $this->setBasePrice($csv[1][1]);

            // read all the rows, starting from 2nd and create all the uplifts
            for ($i=2; $i < count($csv); $i++) {
                $dec = str_replace('%', '', $csv[$i][2]) / 100;
                $this->addUplift($csv[$i][0], $dec);
            }

            // create number of child companies
            for ($i=0; $i < $numOfCompanies; $i++) {
                // pick random type for each child company
                $type = array_rand($this->uplifts);

                $this->addChildCompany($type);
            }

            return true;
        }

        return false;
    }

    /**
     * sets a base price for a gold bar
     * @param int $basePrice
     */
    public function setBasePrice(int $basePrice)
    {
        $this->basePrice = $basePrice;
    }

    /**
     * adds an uplift percentage to the list
     * @param string $type       type of a company
     * @param float $percentage  percentage as a float value to apply to a gold bar base price
     */
    public function addUplift(string $type, float $percentage)
    {
        $type = str_replace(' ', '', $type);

        $this->uplifts[$type] = $percentage;
    }

    /**
     * get list of all the percentage markups that are applied to base price of gold bar
     * @param  boolean $json specifies if returned value should json encoded
     * @return mixed   json|array
     */
    public function getUplifts(bool $json = true)
    {
        return $json ? json_encode($this->uplifts, JSON_PRETTY_PRINT) : $this->uplifts;
    }

    /**
     * adds a child company
     * @param string $type
     */
    public function addChildCompany(string $type)
    {
        if ($this->isCompanyTypeVaild($type)) {
            return $this->childCompanies[] = $this->createChildCompany($type);
        }
    }

    private function createChildCompany(string $type)
    {
        return new ChildCompany($type);
    }

    /**
     * Check if company type exists in the list (is valid)
     * @param  string  string child company type
     * @return boolean
     */
    private function isCompanyTypeVaild(string $type)
    {
        return array_key_exists($type, $this->uplifts);
    }

    /**
     * produces total cumulative billed amount for the given period length to all customers
     * @param  integer $length
     * @return string returns a string containing the JSON representation
     */
    public function getTotalBill($length = 12)
    {
        $total = 0;

        foreach($this->childCompanies as $childCompany)
        {
            $total += $this->billProjectionForSingleCompany($childCompany->type, $childCompany->avgGoldBarsPerMonth, $length);
        }

        $bill['name'] = 'total cumulative billed amount to all customers';
        $bill['period'] = $length . ' months';
        $bill['total'] = "£".$total;

        return json_encode($bill, JSON_PRETTY_PRINT);
    }

    /**
     * produces total that would be billed to each company type within a given period
     * @param  integer $avgGoldBarsPerMonth how many gold bars are bought/sold in a month
     * @param  integer $length              period length
     * @return string returns a string containing the JSON representation
     */
    public function billProjection(int $avgGoldBarsPerMonth = 1, int $length = 12)
    {
        $total = 0;

        $bill['name'] = 'total billed to each company type';
        $bill['period'] = $length . ' months';

        foreach ($this->uplifts as $companyType => $uplift) {
            $projection = $this->billProjectionForSingleCompany($companyType, $avgGoldBarsPerMonth, $length);
            $total += $projection;

            $bill['totals'][$companyType] = "£".$projection;
        }

        $bill['total'] = "£".$total;

        return json_encode($bill, JSON_PRETTY_PRINT);
    }

    /**
     * calculates projected billing amount over specifed period length
     * based on the uplifted sale price, and number of gold bars sold in a month
     * @param  string $companyType
     * @param  int $avgGoldBarsPerMonth
     * @param  int $length
     * @return mixed
     */
    private function billProjectionForSingleCompany(string $companyType, int $avgGoldBarsPerMonth, int $length)
    {
        return ($this->uplifts[$companyType] + 1) * $this->basePrice * $avgGoldBarsPerMonth * $length;
    }
}
