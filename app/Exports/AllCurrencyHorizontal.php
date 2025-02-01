<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AllCurrencyHorizontal implements FromView
{
    protected $data;
    protected $types;

    public function __construct(array $data, array $types)
    {
        $this->data = $data;
        $this->types = $types;
    }

    public function view(): View
    {
        return view('Admin.Master.AllCurHorizontalExcel', [
            'data' => $this->data,
            'types' => $this->types,
        ]);
    }
}
