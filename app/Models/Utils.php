<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Utils
{
    public function getExchParty($exch_party = null)
    {
        $exch_builder = DB::table('party');
        $exch_builder->select('*');
        if (!empty($exch_party) && isset($exch_party)) {
            $exch_partyName = 'EXCH_' . $exch_party;
            $exch_builder->where('party_name', $exch_partyName);
        } else {
            $users = $this->getUsers();
            $userNames = array_map(function ($user) {
                return 'EXCH_' . $user->user_name;
            }, $users);
            $exch_builder->whereIn('party_name', $userNames);
            $exch_builder->orWhere('party_name', 'EXCH');
        }
        $result = $exch_builder->get()->toArray();

        return $result;
    }

    public function getPartyData($table, $party)
    {
        $data = DB::table($table);
        $data->select('*');
        $data->orderBy('srn', 'DESC');
        $data->where('dr_party', $party);
        $data->orWhere('cr_party', $party);
        $result = $data->first();

        return $result;
    }

    public function getConversionInfo($fromCurrency, $toCurrency, $fromAmount = '', $toAmount = '')
    {
        $info = 0;
        $actual_rate = '';

        if ($fromCurrency == 'inr' && $toCurrency == 'aed') {
            $info = 1;
            if ($fromAmount !== '' && $toAmount !== '') {
                $actual_rate = round($fromAmount / $toAmount, 2);
            }

        } elseif ($fromCurrency == 'aed' && $toCurrency == 'inr') {
            $info = 2;
            if ($fromAmount !== '' && $toAmount !== '') {
                $actual_rate = round($toAmount / $fromAmount, 2);
            }

        } elseif ($fromCurrency == 'inr' && $toCurrency == 'usd') {
            $info = 3;
            if ($fromAmount !== '' && $toAmount !== '') {
                $actual_rate = round($fromAmount / $toAmount, 2);
            }

        } elseif ($fromCurrency == 'usd' && $toCurrency == 'inr') {
            $info = 4;
            if ($fromAmount !== '' && $toAmount !== '') {
                $actual_rate = round($toAmount / $fromAmount, 2);
            }
            // info 5 is for commission
        } elseif ($fromCurrency == 'aed' && $toCurrency == 'usd') {
            $info = 6;
            if ($fromAmount !== '' && $toAmount !== '') {
                $actual_rate = round($fromAmount / $toAmount, 2);
            }

        } elseif ($fromCurrency == 'usd' && $toCurrency == 'aed') {
            $info = 7;
            if ($fromAmount !== '' && $toAmount !== '') {
                $actual_rate = round($toAmount / $fromAmount, 2);
            }

        }
        return ['info' => $info, 'actual_rate' => $actual_rate];
    }

    public function getUsers()
    {
        $builder = DB::table('users');
        $builder->select('id', 'user_name');

        return $builder->get()->toArray();
    }
}
