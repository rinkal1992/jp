<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllStatementExcelExport implements WithMultipleSheets
{
    protected $currencies;

    public function __construct($currencies)
    {
        $this->currencies = $currencies;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->currencies as $currency => $data) {
            $sheets[] = new CurrencySheetExport($currency, $data);
        }

        return $sheets;
    }
}
