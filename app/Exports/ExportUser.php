<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportUser implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $stringtable = config('global.transction_type_list');
        $data = DB::table('party as p');
        $data->join('users as u', 'u.id', '=', 'p.user_id');
        $data->select('p.srn', 'u.user_name', 'p.party_name', 'p.ac_number', 'p.details');
        $partyarray = $data->get();

        $transaction_data = array();
        foreach ($partyarray as $party) {
            $result['srn'] = $party->srn;
            $result['party_name'] = $party->party_name;
            foreach ($stringtable as $table_type) {
                $data = DB::table("transaction_$table_type");
                $data->orderBy('srn', 'DESC');
                $data->where('dr_party', $party->srn);
                $data->orWhere('cr_party', $party->srn);
                $trans_array_inr = $data->first();

                if (empty($trans_array_inr)) {
                    $result[$table_type . '_amount'] = 0;
                } else {
                    if ($trans_array_inr->dr_party == $party->srn) {
                        $result[$table_type . '_amount'] = $trans_array_inr->dr_party_balance;
                    } else if ($trans_array_inr->cr_party == $party->srn) {
                        $result[$table_type . '_amount'] = $trans_array_inr->cr_party_balance;
                    } else {
                        $result[$table_type . '_amount'] = 0;
                    }
                }
            }
            $transaction_data[] = $result;
        }
        $final_data = collect($transaction_data);
        return $final_data;
    }

    public function headings(): array
    {
        $headings = ['Srn', 'Party Name'];

        $types = config('global.transction_type_list');
        foreach ($types as $type) {
            $headings[] = strtoupper($type) . ' Balance';
        }

        return $headings;
    }
}
