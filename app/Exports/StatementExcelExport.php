<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StatementExcelExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        // Return the filtered and formatted data to be exported
        return collect($this->data);
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
}
