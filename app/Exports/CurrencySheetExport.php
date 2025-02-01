<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CurrencySheetExport implements FromArray, WithHeadings, WithTitle
{
    protected $currency;
    protected $data;

    public function __construct($currency, $data)
    {
        $this->currency = $currency;
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Srn',
            'Date',
            'User',
            'Description',
            'Debit',
            'Credit',
            'Closing Balance',
        ];
    }

    public function title(): string
    {
        return strtoupper($this->currency);
    }
}
