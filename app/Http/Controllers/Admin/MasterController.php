<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AllCurrencyHorizontal;
use App\Http\Controllers\Controller;
use App\Models\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportUser;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\StatementExcelExport;
use App\Exports\AllStatementExcelExport;

class MasterController extends Controller
{

    private $utils;

    public function __construct()
    {
        $this->utils = new Utils();
    }

    public function party_list(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('party as p')
                ->join('users as u', 'u.id', '=', 'p.user_id')
                ->select('p.srn', 'u.user_name', 'p.party_name', 'p.ac_number', 'p.details')
                ->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->make(true);
        }
        $data['title'] = 'party_list';
        return view('Admin.Master.party_list', $data);
    }


    public function add_group(Request $req)
    {
        $rules = array(
            'group_name' => 'required',
        );
        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->toArray()]);
        } else {
            $data = array(
                'timest' => date('Y-m-d H:i:s'),
                'user_id' => Auth::User()->id,
                'group_name' => $req->group_name,
            );

            if (!empty($data)) {
                $insert = DB::table('groups')->insert($data);
            }
            if ($insert) {
                return response()->json(['st' => 'success', 'msg' => 'Group Added',]);
            } else {
                return response()->json(['st' => 'success', 'msg' => 'Failed to add',]);
            }
        }
    }

    public function getGroups(Request $request)
    {
        $search = $request->searchTerm;
        if ($search == '') {
            $builder = DB::table('groups');
            $builder->select('id', 'group_name');
            $groups = $builder->get();
        } else {
            $builder = DB::table('groups');
            $builder->select('id', 'group_name');
            $builder->where('group_name', 'like', '%' . $search . '%');
            $builder->limit(10);
            $groups = $builder->get();
        }

        $response = array();
        foreach ($groups as $group) {
            $response[] = array(
                "id" => $group->id,
                "text" => $group->group_name
            );
        }
        return response()->json($response);
    }

    public function group_wise_data(Request $request)
    {
        $stringtable = config('global.transction_type_list');
        $data_builder = DB::table('party as p');
        $data_builder->join('users as u', 'u.id', '=', 'p.user_id');
        $data_builder->select('p.srn', 'u.user_name', 'p.party_name', 'p.ac_number', 'p.details', 'p.group_id');
        if ($request->single_party == "single_party" && !empty($request->srn)) {
            $data_builder->where('srn', $request->srn);
        }
        $partyarray = $data_builder->get();

        $transaction_data = array();
        $grouped_transactions = array();
        foreach ($partyarray as $party) {
            $group_id = $party->group_id;
            $result['srn'] = $party->srn;
            $result['party_name'] = $party->party_name;
            $result['group_id'] = $party->group_id;
            foreach ($stringtable as $table_type) {
                $data_Qbuilder = DB::table("transaction_$table_type");
                $data_Qbuilder->orderBy('srn', 'DESC');
                $data_Qbuilder->where('dr_party', $party->srn);
                $data_Qbuilder->orWhere('cr_party', $party->srn);
                $trans_array_inr = $data_Qbuilder->first();

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
            if ($request->hide_zero == 'true') {
                if ($result['inr_amount'] == 0 && $result['usd_amount'] == 0 && $result['aed_amount'] == 0) {
                    // Skip zero transactions
                } else {
                    $grouped_transactions[$group_id][] = $result;
                }
            } else {
                $grouped_transactions[$group_id][] = $result;
            }
        }

        foreach ($grouped_transactions as $group_id => &$transactions) {
            $group_name = DB::table('groups')->where('id', $group_id)->value('group_name');
            if (empty($group_name)) {
                $group_name = 'Default';
            }

            $total_inr = 0;
            $total_usd = 0;
            $total_aed = 0;

            foreach ($transactions as &$transaction) {
                $total_inr += $transaction['inr_amount'];
                $total_usd += $transaction['usd_amount'];
                $total_aed += $transaction['aed_amount'];
            }

            $group_totals[$group_id]['total_inr'] = $total_inr;
            $group_totals[$group_id]['total_usd'] = $total_usd;
            $group_totals[$group_id]['total_aed'] = $total_aed;

            $transactions['total_inr'] = $total_inr;
            $transactions['total_usd'] = $total_usd;
            $transactions['total_aed'] = $total_aed;
            $transactions['group_name'] = $group_name;
        }

        $data['title'] = "party_report";
        return view('Admin.Master.group_wise_party_report', $data, compact('grouped_transactions'));
    }


    public function add_party(Request $req)
    {
        $rules = array(
            'party_name' => 'required',
            'party_details' => 'required',
        );
        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->toArray()]);
        } else {
            $party_builder = DB::table("party");
            $party_builder->select('ac_number');
            $party_builder->orderBy('srn', 'DESC');
            $party_ac_number = $party_builder->first();

            if (empty($party_ac_number)) {
                $ac_number = 100;
            } else {
                $ac_number = $party_ac_number->ac_number + 1;
            }
            $data = array(
                'timest' => date('Y-m-d H:i:s'),
                'user_id' => Auth::User()->id,
                'party_name' => $req->party_name,
                'ac_number' => $ac_number,
                'details' => $req->party_details,
                'group_id' => $req->group_id ? $req->group_id : 0,
            );
            if (!empty($data)) {
                $insert = DB::table('party')->insert($data);
            }
            if ($insert) {
                return response()->json(['st' => 'success', 'msg' => 'Party Added',]);
            } else {
                return response()->json(['st' => 'success', 'msg' => 'Failed to add',]);
            }
        }
    }

    public function transaction(Request $request, $type)
    {
        if (Schema::hasTable("transaction_$type")) {
            if ($request->ajax()) {
                $data = DB::table("transaction_$type as t")
                    ->join('users as u', 'u.id', '=', 't.user_id')
                    ->join('party as pd', 'pd.srn', '=', 't.dr_party')
                    ->join('party as pc', 'pc.srn', '=', 't.cr_party')
                    ->select('t.srn', 't.timest', 'pd.party_name as dr_party', 'pc.party_name as cr_party', 'u.user_name', 't.amount', 't.note', 't.dr_party_balance', 't.cr_party_balance', 't.info', 't.map')
                    ->orderBY('srn', 'desc')
                    ->get();
                $currentDateTime = Carbon::now();
                $last15Minutes = $currentDateTime->subMinutes(60);
                @$lastSrn = $data[0]->srn;
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('timest', function ($row) {
                        $date = date("d-m-Y", strtotime($row->timest));
                        return $date;
                    })
                    ->editColumn('srn', function ($row) use ($lastSrn, $type, $last15Minutes) {
                        $timest = Carbon::parse($row->timest);
                        $isEditable = $timest->greaterThan($last15Minutes);
                        if ($isEditable && $row->srn === $lastSrn) {
                            return '<span style="color: red; cursor: pointer;" onclick="delete_entry(this)" data-srn="' . $row->srn . '" data-table_name="' . $type . '" data-info="' . $row->info . '"data-map="' . $row->map . '">' . $row->srn . '</span>';
                        }
                        return $row->srn;
                    })
                    ->rawColumns(['srn'])
                    ->make(true);
            }
            $data['title'] = 'transaction_' . $type;
            $data['type'] = $type;
            return view('Admin.Master.transaction', $data);
        } else {
            return abort(404);
        }
    }

    public function submit_transaction(Request $req)
    {
        $rules = array(
            'dr_party' => 'required',
            'cr_party' => 'required',
            'tras_amount' => 'required|numeric',
            'note' => 'required',
            'type' => 'required',
        );
        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->toArray()]);
        } else {
            $drRemain = round($req->dr_amount) - round($req->tras_amount);
            $crRemain = round($req->cr_amount) + round($req->tras_amount);
            $type = $req->type;
            $drid = $req->dr_party;
            $crid = $req->cr_party;
            $stringtable = "transaction_$type";
            $data = array(
                'timest' => date('Y-m-d H:i:s'),
                'user_id' => Auth::User()->id,
                'dr_party' => $req->dr_party,
                'cr_party' => $req->cr_party,
                'amount' => $req->tras_amount,
                'note' => $req->note,
                'dr_party_balance' => $drRemain,
                'cr_party_balance' => $crRemain,
                'info' => @$req->trans_type == 'commission' ? 5 : 0
            );
            $drdata = $this->checkAmount($type, $drid);
            $crdata = $this->checkAmount($type, $crid);
            if ($req->dr_amount == $drdata && $req->cr_amount == $crdata) {
                $insertID = DB::table($stringtable)->insertGetId($data);
                $retrivedata = DB::table("transaction_$type as t")
                    ->join('users as u', 'u.id', '=', 't.user_id')
                    ->join('party as pd', 'pd.srn', '=', 't.dr_party')
                    ->join('party as pc', 'pc.srn', '=', 't.cr_party')
                    ->select('t.srn', 'pd.party_name as dr_party', 'pc.party_name as cr_party', 'u.user_name')
                    ->where('t.srn', $insertID)
                    ->first();
                $receipt = array(
                    'srn' => (strtoupper($type) . '_' . strval($insertID)),
                    'date' => date('d-m-Y'),
                    'dr_party' => $retrivedata->dr_party,
                    'cr_party' => $retrivedata->cr_party,
                    'note' => $req->note,
                    'amount' => $req->tras_amount,
                    'user' => Auth::User()->user_name,
                    'type' => $type
                );
                $msg = ['st' => 'success', 'msg' => $receipt];
            } else {
                $msg = ['st' => 'failed', 'msg' => 'Something went wrong Please try again later!'];
            }
        }
        return response()->json($msg);
    }

    private function checkAmount($type, $request)
    {
        if (!empty($request)) {
            $stringtable = "transaction_$type";
            $data = DB::table($stringtable)
                ->select('srn', 'dr_party_balance', 'cr_party_balance', 'dr_party', 'cr_party')
                ->orderBy('srn', 'DESC')
                ->where('dr_party', $request)
                ->orWhere('cr_party', $request)
                ->first();
            if (empty($data)) {
                $result = 0;
            } else {
                if ($data->dr_party == $request) {
                    $result = $data->dr_party_balance;
                } else if ($data->cr_party == $request) {
                    $result = $data->cr_party_balance;
                } else {
                    $result = 0;
                }
            }
        } else {
            $result = 0;
        }
        return $result;
    }

    public function getDrparty(Request $request)
    {
        $search = $request->searchTerm;
        if ($search == '') {
            $builder = DB::table('party');
            $builder->where('srn', '!=', $request->cr_party);
            if (empty($request->type)) {
                $builder->where('party_name', '!=', 'EXCH');
            }
            $builder->select('srn', 'party_name');
            $drParties = $builder->get();
        } else {
            $builder = DB::table('party');
            $builder->select('srn', 'party_name');
            $builder->where('srn', '!=', $request->cr_party);
            $builder->where('party_name', 'like', '%' . $search . '%');
            if (empty($request->type)) {
                $builder->where('party_name', '!=', 'EXCH');
            }
            $builder->limit(10);
            $drParties = $builder->get();
        }

        $response = array();
        foreach ($drParties as $drParty) {
            $response[] = array(
                "id" => $drParty->srn,
                "text" => $drParty->party_name
            );
        }
        return response()->json($response);
    }

    public function getCrparty(Request $request)
    {
        $search = $request->searchTerm;
        if ($search == '') {
            $builder = DB::table('party');
            $builder->where('srn', '!=', $request->dr_party);
            if (empty($request->type)) {
                $builder->where('party_name', '!=', 'EXCH');
            }
            $builder->select('srn', 'party_name');
            $crParties = $builder->get();
        } else {
            $builder = DB::table('party');
            $builder->select('srn', 'party_name');
            $builder->where('srn', '!=', $request->dr_party);
            $builder->where('party_name', 'like', '%' . $search . '%');
            if (empty($request->type)) {
                $builder->where('party_name', '!=', 'EXCH');
            }
            $builder->limit(10);
            $crParties = $builder->get();
        }

        $response = array();
        foreach ($crParties as $crParty) {
            $response[] = array(
                "id" => $crParty->srn,
                "text" => $crParty->party_name
            );
        }
        return response()->json($response);
    }

    public function getUser(Request $request)
    {
        $search = $request->searchTerm;
        if ($search == '') {
            $getUsers = DB::table('users')->select('id', 'user_name')->get();
        } else {
            $getUsers = DB::table('users')->select('id', 'user_name')->where('user_name', 'like', '%' . $search . '%')->limit(10)->get();
        }

        $response = array();
        foreach ($getUsers as $getUser) {
            $response[] = array(
                "id" => $getUser->id,
                "text" => $getUser->user_name
            );
        }
        return response()->json($response);
    }

    public function getAmount(Request $request)
    {
        $type = $request->type;
        $id = $request->id;
        $result = $this->checkAmount($type, $id);
        return response()->json($result);
    }

    public function report_list(Request $request)
    {
        $type = $request->type;
        $stringtable = "transaction_$type as t";
        if ($request->ajax()) {
            $data = DB::table($stringtable);
            $data->join('users as u', 'u.id', '=', 't.user_id');
            $data->join('party as pd', 'pd.srn', '=', 't.dr_party');
            $data->join('party as pc', 'pc.srn', '=', 't.cr_party');
            $data->select('t.srn', 'pd.party_name as dr_party', 'pc.party_name as cr_party', 'u.user_name', 't.amount', 't.note', 't.dr_party_balance', 't.cr_party_balance', 'u.id');
            if ($request->dr_party) {
                $data->where('t.dr_party', $request->dr_party);
            }
            if ($request->cr_party) {
                $data->where('t.cr_party', $request->cr_party);
            }
            if ($request->dr_party && $request->cr_party) {
                $data->where('t.dr_party', $request->dr_party);
                $data->orWhere('t.cr_party', $request->cr_party);
            }
            if ($request->user) {
                $data->where('t.user_id', $request->user);
            }
            $data->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->make(true);
        }
        $data['title'] = "report_list";
        return view('Admin.Master.report_list', $data);
    }

    public function all_statement_list(Request $req)
    {
        if ($req->ajax()) {
            $closing_bal = array();
            $stringtable = "transaction_$req->type as t";
            if ($req->ajax()) {
                $builder = DB::table($stringtable);
                $builder->join('users as u', 'u.id', '=', 't.user_id');
                $builder->join('party as pd', 'pd.srn', '=', 't.dr_party');
                $builder->join('party as pc', 'pc.srn', '=', 't.cr_party');
                $builder->select('t.srn', 't.timest', 'u.user_name', 't.amount', 't.note', 't.dr_party_balance', 't.cr_party_balance', 't.dr_party', 't.cr_party', 'pd.party_name as dr_party_name', 'pc.party_name as cr_party_name');
                $selections = array($req->party);
                $builder->where(function ($query) use ($selections) {
                    foreach ($selections as $selection) {
                        $query->where('t.dr_party', $selection);
                        $query->orWhere('t.cr_party', $selection);
                    }
                });
                if (!empty($req->fromdate && $req->todate)) {
                    $fromdate = Carbon::parse($req->fromdate);
                    $todate = Carbon::parse($req->todate);
                    $builder->whereDate('t.timest', '>=', $fromdate->format('Y-m-d'));
                    $builder->whereDate('t.timest', '<=', $todate->format('Y-m-d'));
                }
                $result_array = $builder->get();

                if (!empty($result_array)) {
                    foreach ($result_array as $data) {
                        $result['srn'] = $data->srn;
                        $result['date'] = $data->timest;
                        $result['user_name'] = $data->user_name;
                        $result['amount'] = $data->amount;
                        $result['note'] = $data->note;
                        if ($data->dr_party == $req->party) {
                            $result['cr_party_balance'] = '-';
                            $result['dr_party_balance'] = $data->amount;
                            $result['cbalance'] = $data->dr_party_balance;
                            $result['party_name'] = $data->dr_party_name;
                            $result['opp_party_name'] = $data->cr_party_name;
                        } else if ($data->cr_party == $req->party) {
                            $result['cr_party_balance'] = $data->amount;
                            $result['dr_party_balance'] = '-';
                            $result['cbalance'] = $data->cr_party_balance;
                            $result['party_name'] = $data->cr_party_name;
                            $result['opp_party_name'] = $data->dr_party_name;
                        } else {
                            $result['cbalance'] = 0;
                        }
                        $closing_bal[] = $result;
                    }
                }
            }
            $final_data = collect($closing_bal);
            return Datatables::of($final_data)
                ->addIndexColumn()
                ->make(true);
        }
        $data['req_type'] = $req->type;
        $data['title'] = 'all_statement_list';
        return view('Admin.Master.all_statement_list', $data);
    }

    public function party_report(Request $request)
    {
        if ($request->ajax()) {
            $stringtable = config('global.transction_type_list');
            $data = DB::table('party as p');
            $data->join('users as u', 'u.id', '=', 'p.user_id');
            $data->select('p.srn', 'u.user_name', 'p.party_name', 'p.ac_number', 'p.details');
            if ($request->single_party == "single_party" && !empty($request->srn)) {
                $data->where('srn', $request->srn);
            }
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
                if ($request->hide_zero == 'true') {
                    if ($result['inr_amount'] == 0 && $result['usd_amount'] == 0 && $result['aed_amount'] == 0) {
                    } else {
                        $transaction_data[] = $result;
                    }
                } else {
                    $transaction_data[] = $result;
                }
            }
            if ($request->single_party == "single_party" && !empty($request->srn)) {
                return response()->json(['st' => 'success', 'msg' => 'Data fetch success!', 'data' => @$result]);
            }
            return Datatables::of($transaction_data)
                ->addIndexColumn()
                ->addColumn('view', function ($row) {
                    return '<button title="' . $row['party_name'] . '" class="btn btn-link" onclick="view(this)" data-val="' . $row['srn'] . '"><i class="fas fa-camera"></i></button>';
                })
                ->rawColumns(['view'])
                ->make(true);
        }
        $data['title'] = "party_report";
        return view('Admin.Master.party_report', $data);
    }

    public function exchange_currency(Request $request)
    {
        if ($request->ajax()) {
            $new_closing_arr = $this->exch_curr($request);
            return Datatables::of($new_closing_arr)
                ->addIndexColumn()
                ->make(true);
        }
        $types = config('global.transction_type_list');
        $dropdownOptions = $this->generateDropdownOptions($types);
        $users = $this->utils->getUsers();

        $data = [
            'title' => 'exchange_currency',
            'dropdownOptions' => $dropdownOptions,
            'users' => $users
        ];
        return view('Admin.Master.exchange_currency', $data);
    }

    private function generateDropdownOptions($types)
    {
        $options = [];
        foreach ($types as $fromCurrency => $fromCurrencyLabel) {
            foreach ($types as $toCurrency => $toCurrencyLabel) {
                if ($fromCurrency !== $toCurrency) {
                    $conversionInfo = $this->utils->getConversionInfo($fromCurrency, $toCurrency);
                    $options[] = [
                        'label' => "{$fromCurrency} to {$toCurrency}",
                        'info' => $conversionInfo['info'],
                    ];
                }
            }
        }
        return $options;
    }

    private function exch_curr($request)
    {
        $types = config('global.transction_type_list');

        if ($request->user) {
            $get_exch = $this->utils->getExchParty($request->user);
        } else {
            $get_exch = $this->utils->getExchParty();
        }

        $new_closing_arr = array();

        foreach ($get_exch as $exch) {
            foreach ($types as $type) {
                $builder = DB::table("transaction_$type as t");
                $builder->join('party as pd', 'pd.srn', '=', 't.dr_party');
                $builder->join('party as pc', 'pc.srn', '=', 't.cr_party');
                $builder->join('users as u', 'u.id', '=', 't.user_id');
                $builder->select('t.srn', 't.timest', 't.amount', 't.note', 't.cr_party_balance', 't.dr_party', 't.cr_party', 'pd.party_name as dr_party_name', 'pc.party_name as cr_party_name', 't.actual_rate', 'u.user_name');
                $builder->where('t.info', '!=', 5);

                $selections = array($exch->srn);

                $builder->where(function ($query) use ($selections) {
                    foreach ($selections as $selection) {
                        $query->where('t.dr_party', $selection);
                        $query->orWhere('t.cr_party', $selection);
                    }
                });

                if (!empty($request->currency_dropdown)) {
                    $builder->where('t.info', $request->currency_dropdown);
                }

                if (!empty($request->fromdate)) {
                    $fromdate = Carbon::parse($request->fromdate);
                    $builder->whereDate('t.timest', '>=', $fromdate->format('Y-m-d'));
                }

                if (!empty($request->todate)) {
                    $todate = Carbon::parse($request->todate);
                    $builder->whereDate('t.timest', '<=', $todate->format('Y-m-d'));
                }

                $closing_arr[$type] = $builder->get();
                $closing_arr_size[$type] = sizeof(($closing_arr[$type]));

                foreach ($closing_arr[$type] as $result) {
                    $new_arr = array(
                        'srn' => $result->srn,
                        'timest' => $result->timest,
                        'user_name' => $result->user_name,
                        'note' => $result->note,
                        'actual_rate' => $result->actual_rate,
                        $type . '_amount' => $result->amount,
                    );

                    if ($result->dr_party == $exch->srn) {
                        $new_arr['party_name'] = $result->cr_party_name;
                    } elseif ($result->cr_party == $exch->srn) {
                        $new_arr['party_name'] = $result->dr_party_name;
                    }

                    foreach ($types as $ntype) {
                        if ($type != $ntype) {
                            $new_arr[$ntype . '_amount'] = ' - ';
                        }
                    }

                    $new_closing_arr[] = $new_arr;
                }
            }
        }
        return $new_closing_arr;
    }


    public function getExchTotal(Request $request)
    {
        if ($request->ajax()) {
            $new_closing_arr = $this->exch_curr($request);
            if (!empty($new_closing_arr)) {
                $types = config('global.transction_type_list');
                $total = array();
                foreach ($types as $type) {
                    $total[$type . '_total'] = 0;
                    foreach ($new_closing_arr as $data) {
                        $total[$type . '_total'] += (int)$data[$type . '_amount'];
                    }
                }
                return response()->json($total);
            }
        }
    }

    public function submit_exchange_currency(Request $req)
    {
        $rules = array(
            'party' => 'required',
            'from_currency' => 'required',
            'from_amount' => 'required',
            'rate_amount' => 'required',
            'to_currency' => 'required',
            'to_amount' => 'required'
        );
        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->toArray()]);
        } else {
            $get_exch = $this->utils->getExchParty(Auth::User()->user_name);

            if (!empty($get_exch)) {
                $ExchSrn = $get_exch[0]->srn;
                $ConversionInfo = $this->utils->getConversionInfo($req->from_currency, $req->to_currency, $req->from_amount, $req->to_amount);

                $table = "transaction_$req->from_currency";
                $to_table = "transaction_$req->to_currency";

                $result = $this->utils->getPartyData($table, $req->party);

                if (empty($result)) {
                    $party_bal = 0;
                } else if ($result->dr_party == $req->party) {
                    $party_bal = $result->dr_party_balance;
                } else if ($result->cr_party == $req->party) {
                    $party_bal = $result->cr_party_balance;
                } else {
                    $party_bal = 0;
                }

                $exch_party = $this->utils->getPartyData($table, $ExchSrn);

                if (empty($exch_party)) {
                    $exchparty_bal = 0;
                } else if ($exch_party->dr_party == $ExchSrn) {
                    $exchparty_bal = $exch_party->dr_party_balance;
                } else if ($exch_party->cr_party == $ExchSrn) {
                    $exchparty_bal = $exch_party->cr_party_balance;
                } else {
                    $exchparty_bal = 0;
                }

                $exch_remain_bal = $exchparty_bal + round($req->from_amount);
                $remain_bal = $party_bal - round($req->from_amount);

                $insert_data = array(
                    'timest' => date('Y-m-d H:i:s'),
                    'user_id' => Auth::User()->id,
                    'dr_party' => $req->party,
                    'cr_party' => $ExchSrn,
                    'amount' => $req->from_amount,
                    'note' => "exch " . $req->from_currency . "_" . $req->to_currency . " " . $req->from_amount . " x " . $req->rate_amount . " = " . $req->to_amount,
                    'dr_party_balance' => $remain_bal,
                    'cr_party_balance' => $exch_remain_bal,
                    'info' => $ConversionInfo['info'],
                    'actual_rate' => $ConversionInfo['actual_rate']
                );

                $to_result = $this->utils->getPartyData($to_table, $req->party);

                if (empty($to_result)) {
                    $toparty_bal = 0;
                } else if ($to_result->dr_party == $req->party) {
                    $toparty_bal = $to_result->dr_party_balance;
                } else if ($to_result->cr_party == $req->party) {
                    $toparty_bal = $to_result->cr_party_balance;
                } else {
                    $toparty_bal = 0;
                }

                $toexch_result = $this->utils->getPartyData($to_table, $ExchSrn);

                if (empty($toexch_result)) {
                    $toexchparty_bal = 0;
                } else if ($toexch_result->dr_party == $ExchSrn) {
                    $toexchparty_bal = $toexch_result->dr_party_balance;
                } else if ($toexch_result->cr_party == $ExchSrn) {
                    $toexchparty_bal = $toexch_result->cr_party_balance;
                } else {
                    $toexchparty_bal = 0;
                }

                $toexch_remain_bal = ($toexchparty_bal - (round($req->to_amount)));
                $to_remain_bal = ($toparty_bal + (round($req->to_amount)));

                $to_insert_data = array(
                    'timest' => date('Y-m-d H:i:s'),
                    'user_id' => Auth::User()->id,
                    'cr_party' => $req->party,
                    'dr_party' => $ExchSrn,
                    'amount' => $req->to_amount,
                    'note' => "exch " . $req->from_currency . "_" . $req->to_currency . " " . $req->from_amount . " x " . $req->rate_amount . " = " . $req->to_amount,
                    'dr_party_balance' => $toexch_remain_bal,
                    'cr_party_balance' => $to_remain_bal,
                    'info' => $ConversionInfo['info'],
                    'actual_rate' => $ConversionInfo['actual_rate']
                );

                if (!empty($insert_data && $to_insert_data)) {
                    $from_insert = DB::table($table)->insertGetId($insert_data);
                    $to_insert = DB::table($to_table)->insertGetId($to_insert_data);

                    $MapId = Str::random(10);

                    $checkExists = DB::table($table)->where('map', $MapId)->exists();
                    if ($checkExists) {
                        $MapId = Str::random(10);
                    }

                    DB::table($table)
                        ->where('srn', $from_insert)
                        ->update(['map' => $req->to_currency . '_' . $MapId]);

                    DB::table($to_table)
                        ->where('srn', $to_insert)
                        ->update(['map' => $req->from_currency . '_' . $MapId]);

                    $builder = DB::table("party");
                    $builder->where('srn', $req->party);
                    $party_name = $builder->first();
                    $receipt = array(
                        'from_srn' => (strtoupper($req->from_currency) . '_' . strval($from_insert)),
                        'to_srn' => (strtoupper($req->to_currency) . '_' . strval($to_insert)),
                        'date' => date('d-m-Y'),
                        'party' => $party_name->party_name,
                        'from_amount' => $req->from_amount,
                        'amount' => $req->to_amount,
                        'note' => "exch " . $req->from_currency . "_" . $req->to_currency . " " . $req->from_amount . " x " . $req->rate_amount . " = " . $req->to_amount . " (" . $ConversionInfo['actual_rate'] . ")",
                        'user' => Auth::User()->user_name,
                    );
                } else {
                    $msg = array("st" => "failed", "msg" => "Failed");
                }
                if ($from_insert && $to_insert) {
                    $msg = array("st" => "success", "msg" => $receipt);
                }
            } else {
                $msg = array("st" => "exch", "msg" => "Please Insert exchange party and it's name should be EXCH_" . Auth::User()->user_name);
            }
        }
        return response()->json($msg);
    }

    public function exportBal()
    {
        return Excel::download(new ExportUser, 'party balance.xlsx');
    }

    public function exportStatementExcel(Request $req)
    {
        if (!empty($req)) {
            if ($req->all_cur == 'all') {
                $types = config('global.transction_type_list');
                $closing_bal = array();
                if ($req->view == 'horizontal') {
                    $result = $this->all_currency_horizontal($req, 'excel');
                    if ($result) {
                        foreach ($result as &$obj) {
                            unset($obj['type']);
                            unset($obj['dr_party']);
                            unset($obj['cr_party']);
                            unset($obj['map']);
                            unset($obj['match_id']);
                        }
                        unset($obj);
                    }
                    return Excel::download(new AllCurrencyHorizontal($result, $types), $req->party_name . '_balance.xlsx');
                }
                foreach ($types as $type) {
                    if ($req->ajax()) {
                        $closing_bal = array();
                        $stringtable = "transaction_$type as t";
                        if ($req->ajax()) {
                            $builder = DB::table($stringtable);
                            $builder->join('users as u', 'u.id', '=', 't.user_id');
                            $builder->join('party as pd', 'pd.srn', '=', 't.dr_party');
                            $builder->join('party as pc', 'pc.srn', '=', 't.cr_party');
                            $builder->orderBy('srn', 'asc');
                            $builder->select('t.srn', 't.timest', 'u.user_name', 't.amount', 't.note', 't.dr_party_balance', 't.cr_party_balance', 't.dr_party', 't.cr_party', 'pd.party_name as dr_party_name', 'pc.party_name as cr_party_name');
                            $selections = array($req->party);
                            $builder->where(function ($query) use ($selections) {
                                foreach ($selections as $selection) {
                                    $query->where('t.dr_party', $selection);
                                    $query->orWhere('t.cr_party', $selection);
                                }
                            });
                            if (!empty($req->fromdate && $req->todate)) {
                                $fromdate = Carbon::parse($req->fromdate);
                                $todate = Carbon::parse($req->todate);
                                $builder->whereDate('t.timest', '>=', $fromdate->format('Y-m-d'));
                                $builder->whereDate('t.timest', '<=', $todate->format('Y-m-d'));
                            }
                            $result_array = $builder->get();

                            if (!empty($result_array)) {
                                foreach ($result_array as $data) {
                                    $result['srn'] = $data->srn;
                                    $result['date'] = $data->timest;
                                    $result['user_name'] = $data->user_name;

                                    $result['note'] = $data->note;
                                    if ($data->cr_party == $req->party) {
                                        $result['cr_party_balance'] = '-';
                                        $result['dr_party_balance'] = $data->amount;
                                        $result['cbalance'] = $data->cr_party_balance;
                                        if (isset($req->show_party)) {
                                            $result['party_name'] = $data->dr_party_name;
                                            $result['opp_party_name'] = $data->cr_party_name;
                                        }
                                    } else if ($data->dr_party == $req->party) {
                                        $result['cr_party_balance'] = $data->amount;
                                        $result['dr_party_balance'] = '-';
                                        $result['cbalance'] = $data->dr_party_balance;
                                    } else {
                                        $result['cbalance'] = 0;
                                    }
                                    $closing_bal[] = $result;
                                }
                            }
                        }
                    }
                    $cur_types['cur_type'][$type] = $closing_bal;
                }

                return Excel::download(new AllStatementExcelExport($cur_types['cur_type']), $req->party_name . '_balance.xlsx');
            } else {
                $result = $this->statement_list($req);
                $data_encode = json_encode($result);
                $data_decode = json_decode($data_encode);

                $partyName['party_name'] = $req->party_name;
                $partyName['type'] = $req->type;
                if (!empty($req->fromdate && $req->todate)) {
                    $partyName['from_date'] = $req->fromdate;
                    $partyName['to_date'] = $req->todate;
                }
                if (isset($req->show_party)) {
                    $data['show_party'] = 1;
                }
                $data = $data_decode->original->data;
                $final_data = array();
                foreach ($data as $object) {
                    unset($object->party_name);
                    unset($object->opp_party_name);
                    unset($object->amount);
                    unset($object->opp_party_name);
                    unset($object->DT_RowIndex);

                    $collect_data['srn'] = $object->srn;
                    $collect_data['date'] = $object->date;
                    $collect_data['user_name'] = $object->user_name;
                    $collect_data['note'] = $object->note;
                    $collect_data['dr_party_balance'] = $object->dr_party_balance;
                    $collect_data['cr_party_balance'] = $object->cr_party_balance;
                    $collect_data['cbalance'] = $object->cbalance;

                    $final_data[] = $collect_data;
                }
                return Excel::download(new StatementExcelExport($final_data), $req->party_name . '_balance.xlsx');
            }
        }
    }

    public function all_currency_horizontal(Request $req, $excel = null)
    {
        if ($req->view == 'horizontal') {
            $types = config('global.transction_type_list');
            $closing_bal = array();
            foreach ($types as $type) {
                if ($req->ajax()) {
                    $stringtable = "transaction_$type as t";
                    $builder = DB::table($stringtable);
                    $builder->join('users as u', 'u.id', '=', 't.user_id');
                    $builder->join('party as pd', 'pd.srn', '=', 't.dr_party');
                    $builder->join('party as pc', 'pc.srn', '=', 't.cr_party');
                    $builder->orderBy('srn', 'asc');
                    $builder->select('t.srn', 't.timest', 'u.user_name', 't.amount', 't.note', 't.dr_party_balance', 't.cr_party_balance', 't.dr_party', 't.cr_party', 'pd.party_name as dr_party_name', 'pc.party_name as cr_party_name', 't.map');
                    $selections = array($req->party);
                    $builder->where(function ($query) use ($selections) {
                        foreach ($selections as $selection) {
                            $query->where('t.dr_party', $selection);
                            $query->orWhere('t.cr_party', $selection);
                        }
                    });
                    if (!empty($req->fromdate && $req->todate)) {
                        $fromdate = Carbon::parse($req->fromdate);
                        $todate = Carbon::parse($req->todate);
                        $builder->whereDate('t.timest', '>=', $fromdate->format('Y-m-d'));
                        $builder->whereDate('t.timest', '<=', $todate->format('Y-m-d'));
                    }
                    $result_array = $builder->get();

                    if (!empty($result_array)) {

                        foreach ($result_array as $data) {
                            $result['srn'] = $data->srn;
                            $result['date'] = $data->timest;
                            $result['user_name'] = $data->user_name;
                            $result['type'] = $type;
                            $result['dr_party'] = $data->dr_party;
                            $result['cr_party'] = $data->cr_party;
                            $result['map'] = $data->map;
                            $result['note'] = $data->note;

                            foreach ($types as $otherType) {
                                if ($otherType !== $type) {
                                    $otherCreditKey = $otherType . '_credit';
                                    $otherDebitKey = $otherType . '_debit';
                                    $otherCbalanceKey = $otherType . '_cbalance';

                                    $result[$otherCreditKey] = ' ';
                                    $result[$otherDebitKey] = ' ';
                                    $result[$otherCbalanceKey] = ' ';
                                }
                                if (!empty($data->map)) {
                                    $mapParts = explode('_', $data->map);
                                    if (count($mapParts) === 2) {
                                        $curtable = $mapParts[0];
                                        $map_srn = $mapParts[1];
                                        $map = $type . '_' . $map_srn;

                                        $currencyTable = "transaction_$curtable";
                                        $currencyData = DB::table($currencyTable)->where('map', $map)->first();

                                        if ($currencyData) {
                                            if ($data->cr_party == $req->party) {
                                                $result[$type . '_debit'] = '-';
                                                $result[$type . '_credit'] = $data->amount;
                                                $result[$type . '_cbalance'] = $data->cr_party_balance;
                                                $result[$curtable . '_debit'] = $currencyData->amount;
                                                $result[$curtable . '_credit'] = '-';
                                                $result[$curtable . '_cbalance'] = $currencyData->dr_party_balance;
                                            } else if ($data->dr_party == $req->party) {
                                                $result[$type . '_debit'] = $data->amount;
                                                $result[$type . '_credit'] = '-';
                                                $result[$type . '_cbalance'] = $data->dr_party_balance;
                                                $result[$curtable . '_debit'] = '-';
                                                $result[$curtable . '_credit'] = $currencyData->amount;
                                                $result[$curtable . '_cbalance'] = $currencyData->cr_party_balance;
                                            }
                                            $result['match_id'] = $map_srn;
                                        }
                                    }
                                } else {
                                    if ($data->cr_party == $req->party) {
                                        $result[$type . '_debit'] = '-';
                                        $result[$type . '_credit'] = $data->amount;
                                        $result[$type . '_cbalance'] = $data->cr_party_balance;
                                    } else if ($data->dr_party == $req->party) {
                                        $result[$type . '_debit'] = $data->amount;
                                        $result[$type . '_credit'] = '-';
                                        $result[$type . '_cbalance'] = $data->dr_party_balance;
                                    }
                                    $result['match_id'] = '';
                                }
                            }
                            $closing_bal[] = $result;
                        }
                    }
                }
            }

            $mergedArray = [];

            foreach ($closing_bal as $item) {
                $matchId = $item['match_id'];

                if (empty($matchId)) {
                    $mergedArray[] = $item;
                } elseif (!isset($mergedArray[$matchId])) {
                    $mergedArray[$matchId] = $item;
                } else {
                    $mergedArray[$matchId] = $item;
                }
            }
            usort($mergedArray, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            $mergedArray = array_values($mergedArray);

            if ($excel) {
                return $mergedArray;
            }

            return Datatables::of($mergedArray)
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function statement_list(Request $req)
    {
        if ($req->ajax()) {
            $closing_bal = array();
            $stringtable = "transaction_$req->type as t";
            if ($req->ajax()) {
                $builder = DB::table($stringtable);
                $builder->join('users as u', 'u.id', '=', 't.user_id');
                $builder->join('party as pd', 'pd.srn', '=', 't.dr_party');
                $builder->join('party as pc', 'pc.srn', '=', 't.cr_party');
                $builder->orderBy('srn', 'asc');
                $builder->select('t.srn', 't.timest', 'u.user_name', 't.amount', 't.note', 't.dr_party_balance', 't.cr_party_balance', 't.dr_party', 't.cr_party', 'pd.party_name as dr_party_name', 'pc.party_name as cr_party_name');
                $selections = array($req->party);
                $builder->where(function ($query) use ($selections) {
                    foreach ($selections as $selection) {
                        $query->where('t.dr_party', $selection);
                        $query->orWhere('t.cr_party', $selection);
                    }
                });
                if (!empty($req->fromdate && $req->todate)) {
                    $fromdate = Carbon::parse($req->fromdate);
                    $todate = Carbon::parse($req->todate);
                    $builder->whereDate('t.timest', '>=', $fromdate->format('Y-m-d'));
                    $builder->whereDate('t.timest', '<=', $todate->format('Y-m-d'));
                }
                $result_array = $builder->get();
                if (!empty($result_array)) {
                    foreach ($result_array as $data) {
                        $result['srn'] = $data->srn;
                        $result['user_name'] = $data->user_name;
                        $result['date'] = $data->timest;
                        $result['amount'] = $data->amount;
                        $result['note'] = $data->note;
                        if ($data->dr_party == $req->party) {
                            $result['cr_party_balance'] = '-';
                            $result['dr_party_balance'] = $data->amount;
                            $result['cbalance'] = $data->dr_party_balance;
                            $result['party_name'] = $data->dr_party_name;
                            $result['opp_party_name'] = $data->cr_party_name;
                        } else if ($data->cr_party == $req->party) {
                            $result['cr_party_balance'] = $data->amount;
                            $result['dr_party_balance'] = '-';
                            $result['cbalance'] = $data->cr_party_balance;
                            $result['party_name'] = $data->cr_party_name;
                            $result['opp_party_name'] = $data->dr_party_name;
                        } else {
                            $result['cbalance'] = 0;
                        }
                        $closing_bal[] = $result;
                    }
                }
            }
            $final_data = collect($closing_bal);
            return Datatables::of($final_data)
                ->addIndexColumn()
                ->make(true);
        }
        $data['req_type'] = $req->type;
        $data['title'] = 'statement_list';
        return view('Admin.Master.statement_list', $data);
    }

    public function all_statementPdf(Request $req)
    {
        $types = config('global.transction_type_list');
        $closing_bal = array();
        if ($req->view == "horizontal") {
            $result = $this->all_currency_horizontal($req, 'excel');
            $partyName['party_name'] = $req->party_name;
            if (!empty($req->fromdate && $req->todate)) {
                $partyName['from_date'] = $req->fromdate;
                $partyName['to_date'] = $req->todate;
            }
            $data['types'] = $types;
            $data['data'] = $result;
            $pdf = PDF::loadView('Admin.Master.AllCurHorizontalPdf', $data, $partyName);
            return $pdf->download($req->party_name . '_Statements.pdf');
        }
        foreach ($types as $type) {
            if ($req->ajax()) {
                $closing_bal = array();
                $stringtable = "transaction_$type as t";
                if ($req->ajax()) {
                    $builder = DB::table($stringtable);
                    $builder->join('users as u', 'u.id', '=', 't.user_id');
                    $builder->join('party as pd', 'pd.srn', '=', 't.dr_party');
                    $builder->join('party as pc', 'pc.srn', '=', 't.cr_party');
                    $builder->orderBy('srn', 'asc');
                    $builder->select('t.srn', 't.timest', 'u.user_name', 't.amount', 't.note', 't.dr_party_balance', 't.cr_party_balance', 't.dr_party', 't.cr_party', 'pd.party_name as dr_party_name', 'pc.party_name as cr_party_name');
                    $selections = array($req->party);
                    $builder->where(function ($query) use ($selections) {
                        foreach ($selections as $selection) {
                            $query->where('t.dr_party', $selection);
                            $query->orWhere('t.cr_party', $selection);
                        }
                    });
                    if (!empty($req->fromdate && $req->todate)) {
                        $fromdate = Carbon::parse($req->fromdate);
                        $todate = Carbon::parse($req->todate);
                        $builder->whereDate('t.timest', '>=', $fromdate->format('Y-m-d'));
                        $builder->whereDate('t.timest', '<=', $todate->format('Y-m-d'));
                    }
                    $result_array = $builder->get();

                    if (!empty($result_array)) {
                        foreach ($result_array as $data) {
                            $result['srn'] = $data->srn;
                            $result['date'] = $data->timest;
                            $result['user_name'] = $data->user_name;
                            $result['amount'] = $data->amount;
                            $result['note'] = $data->note;
                            if ($data->dr_party == $req->party) {
                                $result['cr_party_balance'] = '-';
                                $result['dr_party_balance'] = $data->amount;
                                $result['cbalance'] = $data->dr_party_balance;
                                if (isset($req->show_party)) {
                                    $result['party_name'] = $data->dr_party_name;
                                    $result['opp_party_name'] = $data->cr_party_name;
                                }
                            } else if ($data->cr_party == $req->party) {
                                $result['cr_party_balance'] = $data->amount;
                                $result['dr_party_balance'] = '-';
                                $result['cbalance'] = $data->cr_party_balance;
                                if (isset($req->show_party)) {
                                    $result['party_name'] = $data->cr_party_name;
                                    $result['opp_party_name'] = $data->dr_party_name;
                                }
                            } else {
                                $result['cbalance'] = 0;
                            }
                            $closing_bal[] = $result;
                        }
                    }
                }
            }
            $cur_types['cur_type'][$type] = $closing_bal;
        }
        $partyName['party_name'] = $req->party_name;
        if (!empty($req->fromdate && $req->todate)) {
            $partyName['from_date'] = $req->fromdate;
            $partyName['to_date'] = $req->todate;
        }
        if (isset($req->show_party)) {
            $cur_types['show_party'] = 1;
        }
        $pdf = PDF::loadView('Admin.Master.all_cur_statementPdf', $cur_types, $partyName);
        return $pdf->download($req->party_name . '_Statements.pdf');
    }

    public function statementPdf(Request $req)
    {
        if (!empty($req)) {
            $result = $this->statement_list($req);
            $data_encode = json_encode($result);
            $data_decode = json_decode($data_encode);

            $partyName['party_name'] = $req->party_name;
            $partyName['type'] = $req->type;
            if (!empty($req->fromdate && $req->todate)) {
                $partyName['from_date'] = $req->fromdate;
                $partyName['to_date'] = $req->todate;
            }
            if (isset($req->show_party)) {
                $data['show_party'] = 1;
            }
            $data['statements'] = $data_decode->original->data;
            $pdf = PDF::loadView('Admin.Master.statementPdf', $data, $partyName);
            return $pdf->download($req->party_name . '_Statement.pdf');
        }
    }

    public function statement_checkup(Request $req)
    {
        if ($req->ajax()) {
            $closing_bal = array();
            $error_log = array();
            $parties = DB::table('party')->select('srn as party_id')->get();
            $stringtable = "transaction_$req->type as t";

            foreach ($parties as $key => $party) {
                $builder = DB::table($stringtable);
                $builder->join('users as u', 'u.id', '=', 't.user_id');
                $builder->join('party as pd', 'pd.srn', '=', 't.dr_party');
                $builder->join('party as pc', 'pc.srn', '=', 't.cr_party');
                $builder->orderBy('srn', 'asc');
                $builder->select('t.srn', 't.timest', 'u.user_name', 't.amount', 't.note', 't.dr_party_balance', 't.cr_party_balance', 't.dr_party', 't.cr_party', 'pd.party_name as dr_party_name', 'pc.party_name as cr_party_name');
                $selections = array($party->party_id);
                $builder->where(function ($query) use ($selections) {
                    foreach ($selections as $selection) {
                        $query->where('t.dr_party', $selection);
                        $query->orWhere('t.cr_party', $selection);
                    }
                });
                $result_array = $builder->get();
                if (!empty($result_array) && count($result_array) > 1) {
                    foreach ($result_array as $state_key => $data) {
                        $result = [];
                        $result['srn'] = $data->srn;
                        $result['transfer_amount'] = $data->amount;
                        if ($data->dr_party == $party->party_id) {
                            $result['cr_party_balance'] = $data->cr_party_balance;
                            $result['dr_party_balance'] = $data->dr_party_balance;
                            $result['cbalance'] = $data->dr_party_balance;
                            $result['is_credit'] = false;
                            $result['is_debit'] = true;
                        } else if ($data->cr_party == $party->party_id) {
                            $result['cr_party_balance'] = $data->cr_party_balance;
                            $result['dr_party_balance'] = $data->dr_party_balance;
                            $result['cbalance'] = $data->cr_party_balance;
                            $result['is_credit'] = true;
                            $result['is_debit'] = false;
                        } else {
                            $result['cbalance'] = 0;
                        }
                        if (!empty($result_array[$state_key + 1])) {
                            $state_keyresult = $result_array[$state_key + 1];
                            $result['state_key'] = [];
                            $result['state_key']['srn'] = $state_keyresult->srn;
                            $result['state_key']['dr_party_name'] = $state_keyresult->dr_party_name . ' | ' . $state_keyresult->dr_party;
                            $result['state_key']['cr_party_name'] = $state_keyresult->cr_party_name . ' | ' . $state_keyresult->cr_party;
                            if ($state_keyresult->dr_party == $party->party_id) {
                                $result['state_key']['transfer_amount'] = $state_keyresult->amount;
                                $result['state_key']['dr_party_balance'] = $state_keyresult->dr_party_balance;
                                $result['state_key']['cr_party_balance'] = $state_keyresult->cr_party_balance;
                                $result['state_key']['cbalance'] = $state_keyresult->dr_party_balance;
                                $result['state_key']['is_credit'] = false;
                                $result['state_key']['is_debit'] = true;
                            } else if ($state_keyresult->cr_party == $party->party_id) {
                                $result['state_key']['transfer_amount'] = $state_keyresult->amount;
                                $result['state_key']['cr_party_balance'] = $state_keyresult->cr_party_balance;
                                $result['state_key']['dr_party_balance'] = $state_keyresult->dr_party_balance;
                                $result['state_key']['cbalance'] = $state_keyresult->cr_party_balance;
                                $result['state_key']['is_credit'] = true;
                                $result['state_key']['is_debit'] = false;
                            } else {
                                $result['state_key']['cbalance'] = 0;
                            }
                        }
                        $closing_bal[] = $result;
                    }
                }
            }
            foreach ($closing_bal as $entry) {
                if (isset($entry['state_key'])) {
                    $balance = $entry['cbalance'];
                    if ($entry['state_key']['is_debit'] == true) {
                        if ($entry['state_key']['dr_party_balance'] == $entry['state_key']['cbalance']) {
                            if ($balance - $entry['state_key']['transfer_amount'] != $entry['state_key']['cbalance']) {
                                $entry['state_key']['actual_cbalance'] = $balance - $entry['state_key']['transfer_amount'];
                                $error_log[] = $entry;
                            }
                        }
                    }
                    if ($entry['state_key']['is_credit'] == true) {
                        if ($entry['state_key']['cr_party_balance'] == $entry['state_key']['cbalance']) {
                            if ($balance + $entry['state_key']['transfer_amount'] != $entry['state_key']['cbalance']) {
                                $entry['state_key']['actual_cbalance'] = $balance + $entry['state_key']['transfer_amount'];
                                $error_log[] = $entry;
                            }
                        }
                    }
                }
            }
            $final_data = collect($error_log);
            return Datatables::of($final_data)
                ->addIndexColumn()
                ->make(true);
        }
        $title['title'] = "statement_checkup";
        return view('Admin.Master.statement_checkup', $title);
    }

    public function commission_transfer(Request $req)
    {
        if ($req->ajax()) {
            $type = $req->type;
            $data = DB::table("transaction_$type as t");
            $data->join('party as pd', 'pd.srn', '=', 't.dr_party');
            $data->join('party as pc', 'pc.srn', '=', 't.cr_party');
            $data->select('t.srn', 'pd.party_name as dr_party', 'pc.party_name as cr_party', 't.amount', 't.note', 't.dr_party_balance', 't.cr_party_balance');
            $data->where('t.info', 5);
            if (!empty($req->fromdate && $req->todate)) {
                $fromdate = Carbon::parse($req->fromdate);
                $todate = Carbon::parse($req->todate);
                $data->whereDate('t.timest', '>=', $fromdate->format('Y-m-d'));
                $data->whereDate('t.timest', '<=', $todate->format('Y-m-d'));
            }
            if (!empty($req->fromdate) && empty($req->todate)) {
                $fromdate = Carbon::parse($req->fromdate);
                $todate = Carbon::parse($req->todate);
                $data->whereDate('t.timest', '=', $fromdate->format('Y-m-d'));
            }
            $data->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->make(true);
        }
        $title['title'] = "commission_transfer";
        return view('Admin.Master.commission_transfer', $title);
    }

    public function getPartyBal(Request $req)
    {
        if ($req->ajax()) {
            if ($req->id) {
                $stringtable = config('global.transction_type_list');
                $data = DB::table('party as p');
                $data->join('users as u', 'u.id', '=', 'p.user_id');
                $data->select('p.srn', 'u.user_name', 'p.party_name', 'p.ac_number', 'p.details');
                $data->where('p.srn', $req->id);
                $party = $data->first();
                if ($party) {
                    foreach ($stringtable as $table_type) {
                        $data = DB::table("transaction_$table_type");
                        $data->orderBy('srn', 'DESC');
                        $data->where('dr_party', $party->srn);
                        $data->orWhere('cr_party', $party->srn);
                        $trans_array_inr = $data->first();

                        if (empty($trans_array_inr)) {
                            $result[$table_type . '_partybal'] = 0;
                        } else {
                            if ($trans_array_inr->dr_party == $party->srn) {
                                $result[$table_type . '_partybal'] = $trans_array_inr->dr_party_balance;
                            } else if ($trans_array_inr->cr_party == $party->srn) {
                                $result[$table_type . '_partybal'] = $trans_array_inr->cr_party_balance;
                            } else {
                                $result[$table_type . '_partybal'] = 0;
                            }
                        }
                    }
                    $msg = array('st' => 'success', 'msg' => 'Party Balance', 'data' => $result);
                } else {
                    $msg = array('st' => 'failed', 'msg' => 'Party not found');
                }
            } else {
                $msg = array('st' => 'failed', 'msg' => 'Requested not found');
            }
            return response()->json($msg);
        }
    }

    public function delete_entry(Request $req)
    {
        if ($req) {
            $table_name = "transaction_$req->table_name";
            $builder = DB::table($table_name);
            $builder->select('*');
            $builder->orderBy('srn', 'desc');
            $data = $builder->first();

            if ($data) {
                if (!empty($data->map)) {
                    if ($data->map == $req->map && $data->srn == $req->srn) {
                        $mapParts = explode('_', $data->map);
                        if (count($mapParts) === 2) {
                            $curTable = $mapParts[0];
                            $map_txn = $mapParts[1];
                            $map = $req->table_name . '_' . $map_txn;

                            $opp_currencyTable = "transaction_$curTable";

                            $builder = DB::table($opp_currencyTable);
                            $builder->select('*');
                            $builder->orderBy('srn', 'desc');
                            $result = $builder->first();

                            if ($result) {
                                if ($result->map == $map) {
                                    DB::table($table_name)->where(['srn' => $req->srn, 'map' => $req->map])->delete();
                                    DB::table($opp_currencyTable)->where('map', $map)->delete();

                                    $msg = array('st' => 'success', 'msg' => 'Entry deleted.');
                                    return response()->json($msg);
                                } else {
                                    $msg = array('st' => 'failed', 'msg' => 'Can not delete this Entry.');
                                    return response()->json($msg);
                                }
                            }
                        }
                    }
                } else {
                    if ($data->srn == $req->srn) {
                        DB::table($table_name)->where('srn', $req->srn)->delete();

                        $msg = array('st' => 'success', 'msg' => 'Entry deleted.');
                        return response()->json($msg);
                    } else {

                        $msg = array('st' => 'failed', 'msg' => 'Can not delete this Entry.');
                        return response()->json($msg);
                    }
                }
            }
        }
    }

    public function convert(Request $request, $convert_type, $type)
    {
        if (Schema::hasTable("transaction_$type")) {
            if ($convert_type == 'purchase') {
                $conversionInfo = $this->utils->getConversionInfo('inr', $type);
                $data['fromCurrency'] = 'inr';
                $data['toCurrency'] = $type;
                $data['convert_type'] = $convert_type;

            }
            if ($convert_type == 'sales') {
                $conversionInfo = $this->utils->getConversionInfo($type, 'inr');
                $data['fromCurrency'] = $type;
                $data['toCurrency'] = 'inr';
                $data['convert_type'] = $convert_type;
            }
            if ($request->ajax()) {

                $data = DB::table("transaction_inr as t");
                $data->join('party as p', 'p.srn', '=', 't.dr_party');
                $data->join('users as u', 'u.id', '=', 't.user_id');
                $data->select('t.srn', 't.timest', 't.amount', 'p.party_name', 't.note', 't.cr_party_balance', 'u.user_name');
                $data->where('t.info', $conversionInfo['info']);
                $data->orderBy('srn', 'asc');

                if (!empty($request->fromdate)) {
                    $fromdate = Carbon::parse($request->fromdate);
                    $data->whereDate('t.timest', '>=', $fromdate->format('Y-m-d'));
                }

                if (!empty($request->todate)) {
                    $todate = Carbon::parse($request->todate);
                    $data->whereDate('t.timest', '<=', $todate->format('Y-m-d'));
                }
                if ($request->user) {
                    $data->where('u.user_name', $request->user);
                }
                $final_result = array();
                $inr_result_coll = $data->get();
                $inr_result = $inr_result_coll->toArray();

                $data = DB::table("transaction_$type as t");
                $data->join('party as p', 'p.srn', '=', 't.dr_party');
                $data->join('users as u', 'u.id', '=', 't.user_id');
                $data->select('t.srn', 't.timest', 't.amount as ' . $type . '_amount', 'u.user_name', 'p.party_name');

                $data->where('info', $conversionInfo['info']);
                $data->orderBy('srn', 'asc');

                if (!empty($request->fromdate)) {
                    $fromdate = Carbon::parse($request->fromdate);
                    $data->whereDate('t.timest', '>=', $fromdate->format('Y-m-d'));
                }

                if (!empty($request->todate)) {
                    $todate = Carbon::parse($request->todate);
                    $data->whereDate('t.timest', '<=', $todate->format('Y-m-d'));
                }
                if ($request->user) {
                    $data->where('u.user_name', $request->user);
                }

                $cur_result_coll = $data->get();
                $cur_result = $cur_result_coll->toArray();
                if (!empty($inr_result && $cur_result)) {
                    foreach ($inr_result as $key => $inr_data) {
                        $date = date('d-m-Y', strtotime($inr_data->timest));
                        $result['srn'] = $inr_data->srn;
                        $result['timest'] = $date;
                        $result['amount'] = $inr_data->amount;
                        $result['user_name'] = $inr_data->user_name;
                        $result['party_name'] = $convert_type == 'purchase' ? $inr_data->party_name : $cur_result[$key]->party_name;
                        $result['note'] = $inr_data->note;
                        $result[$type . '_amount'] = $cur_result[$key]->{$type . '_amount'};
                        $final_result[] = $result;
                    }
                    $final__collection = collect($final_result);
                } else {
                    $final__collection = array();
                }
                return Datatables::of($final__collection)
                    ->addIndexColumn()
                    ->make(true);
            }
            $users = $this->utils->getUsers();
            $data['title'] = $convert_type . '_' . $type;
            $data['cur_type'] = $type;
            $data['users'] = $users;
            return view('Admin.Master.convert', $data);
        } else {
            return abort(404);
        }
    }

    public function submit_convert(Request $request)
    {
        if ($request->ajax()) {
            $rules = array(
                'party' => 'required',
                'from_currency' => 'required',
                'to_currency' => 'required',
                'from_amount' => 'required|numeric',
                'rate' => 'required|numeric',
                'to_amount' => 'required|numeric'
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->toArray()]);
            } else {
                $get_exch = $this->utils->getExchParty(Auth::User()->user_name);

                if (!empty($get_exch)) {
                    $ExchSrn = $get_exch[0]->srn;
                    $ConversionInfo = $this->utils->getConversionInfo($request->from_currency, $request->to_currency, $request->from_amount, $request->to_amount);

                    $table = "transaction_$request->from_currency";
                    $to_table = "transaction_$request->to_currency";

                    $result = $this->utils->getPartyData($table, $request->party);

                    if (empty($result)) {
                        $party_bal = 0;
                    } else if ($result->dr_party == $request->party) {
                        $party_bal = $result->dr_party_balance;
                    } else if ($result->cr_party == $request->party) {
                        $party_bal = $result->cr_party_balance;
                    } else {
                        $party_bal = 0;
                    }

                    $exch_party = $this->utils->getPartyData($table, $ExchSrn);

                    if (empty($exch_party)) {
                        $exchparty_bal = 0;
                    } else if ($exch_party->dr_party == $ExchSrn) {
                        $exchparty_bal = $exch_party->dr_party_balance;
                    } else if ($exch_party->cr_party == $ExchSrn) {
                        $exchparty_bal = $exch_party->cr_party_balance;
                    } else {
                        $exchparty_bal = 0;
                    }

                    $from_amount = (round($request->from_amount));
                    $to_amount = (round($request->to_amount));
                    $exch_remain_bal = ($exchparty_bal + $from_amount);
                    $remain_bal = ($party_bal - $from_amount);

                    $insert_data = array(
                        'timest' => date('Y-m-d H:i:s'),
                        'user_id' => Auth::User()->id,
                        'dr_party' => $request->party,
                        'cr_party' => $ExchSrn,
                        'amount' => $from_amount,
                        'note' => "exch " . $request->from_currency . '_' . $request->to_currency . " " . $from_amount . " x " . $request->rate . " = " . $request->to_amount,
                        'dr_party_balance' => $remain_bal,
                        'cr_party_balance' => $exch_remain_bal,
                        'info' => $ConversionInfo['info'],
                        'actual_rate' => $ConversionInfo['actual_rate']
                    );

                    $to_result = $this->utils->getPartyData($to_table, $request->party);

                    if (empty($to_result)) {
                        $toparty_bal = 0;
                    } else if ($to_result->dr_party == $request->party) {
                        $toparty_bal = $to_result->dr_party_balance;
                    } else if ($to_result->cr_party == $request->party) {
                        $toparty_bal = $to_result->cr_party_balance;
                    } else {
                        $toparty_bal = 0;
                    }

                    $toexch_result = $this->utils->getPartyData($to_table, $ExchSrn);

                    if (empty($toexch_result)) {
                        $toexchparty_bal = 0;
                    } else if ($toexch_result->dr_party == $ExchSrn) {
                        $toexchparty_bal = $toexch_result->dr_party_balance;
                    } else if ($toexch_result->cr_party == $ExchSrn) {
                        $toexchparty_bal = $toexch_result->cr_party_balance;
                    } else {
                        $toexchparty_bal = 0;
                    }

                    $to_remain_bal = ($toparty_bal + (round($request->to_amount)));
                    $toexch_remain_bal = ($toexchparty_bal - (round($request->to_amount)));

                    $to_insert_data = array(
                        'timest' => date('Y-m-d H:i:s'),
                        'user_id' => Auth::User()->id,
                        'cr_party' => $request->party,
                        'dr_party' => $ExchSrn,
                        'amount' => $to_amount,
                        'note' => "exch " . $request->from_currency . '_' . $request->to_currency . " " . $from_amount . " x " . $request->rate . " = " . $request->to_amount,
                        'dr_party_balance' => $toexch_remain_bal,
                        'cr_party_balance' => $to_remain_bal,
                        'info' => $ConversionInfo['info'],
                        'actual_rate' => $ConversionInfo['actual_rate']
                    );

                    if (!empty($insert_data && $to_insert_data)) {
                        $from_insert = DB::table($table)->insertGetId($insert_data);
                        $to_insert = DB::table($to_table)->insertGetId($to_insert_data);

                        $MapId = Str::random(10);

                        $checkExists = DB::table($table)->where('map', $MapId)->exists();
                        if ($checkExists) {
                            $MapId = Str::random(10);
                        }

                        DB::table($table)
                            ->where('srn', $from_insert)
                            ->update(['map' => $request->to_currency . '_' . $MapId]);

                        DB::table($to_table)
                            ->where('srn', $to_insert)
                            ->update(['map' => $request->from_currency . '_' . $MapId]);

                        $builder = DB::table("party");
                        $builder->where('srn', $request->party);
                        $party_name = $builder->first();
                        $receipt = array(
                            'from_srn' => strtoupper($request->from_currency) . '_' . $from_insert,
                            'to_srn' => strtoupper($request->to_currency) . '_' . $to_insert,
                            'date' => date('d-m-Y'),
                            'party' => $party_name->party_name,
                            'amount' => $to_amount,
                            'note' => "exch {$request->from_currency}_{$request->to_currency} " . $from_amount . " x " . $request->rate . " = " . $request->to_amount . " (" . $ConversionInfo['actual_rate'] . ")",
                            'user' => Auth::User()->user_name,
                        );
                    } else {
                        $msg = array("st" => "failed", "msg" => "Failed");
                    }
                    if ($from_insert) {
                        $msg = array("st" => "success", "msg" => $receipt);
                    }
                } else {
                    $msg = array("st" => "exch", "msg" => "Please Insert exchange party and it's name should be EXCH_" . Auth::User()->user_name);
                }
            }
            return response()->json($msg);
        }
    }

    public function getpurchaseavg(Request $request)
    {
        if ($request->ajax()) {
            $fromCurrency = $request->from_currency;
            $toCurrency = $request->to_currency;
            $conversionInfo = $this->utils->getConversionInfo($fromCurrency, $toCurrency);

            $data = DB::table("transaction_$fromCurrency as t");
            $data->join('users as u', 'u.id', '=', 't.user_id');
            $data->select('t.srn', 't.timest', 't.amount as ' . $fromCurrency . '_amount', 'u.user_name');
            $data->where('t.info', $conversionInfo['info']);
            $data->orderBy('srn', 'asc');
            if (!empty($request->fromdate)) {
                $fromdate = Carbon::parse($request->fromdate);
                $data->whereDate('t.timest', '>=', $fromdate->format('Y-m-d'));
            }

            if (!empty($request->todate)) {
                $todate = Carbon::parse($request->todate);
                $data->whereDate('t.timest', '<=', $todate->format('Y-m-d'));
            }

            if ($request->user) {
                $data->where('u.user_name', $request->user);
            }

            $inr_result_coll = $data->get();
            $inr_result = $inr_result_coll->toArray();

            $data = DB::table("transaction_$toCurrency as t");
            $data->join('users as u', 'u.id', '=', 't.user_id');
            $data->select('t.srn', 't.timest', 't.amount as ' . $toCurrency . '_amount', 'u.user_name');
            $data->where('t.info', $conversionInfo['info']);
            $data->orderBy('srn', 'asc');
            if (!empty($request->fromdate)) {
                $fromdate = Carbon::parse($request->fromdate);
                $data->whereDate('t.timest', '>=', $fromdate->format('Y-m-d'));
            }

            if (!empty($request->todate)) {
                $todate = Carbon::parse($request->todate);
                $data->whereDate('t.timest', '<=', $todate->format('Y-m-d'));
            }
            if ($request->user) {
                $data->where('u.user_name', $request->user);
            }

            $cur_result_coll = $data->get();
            $cur_result = $cur_result_coll->toArray();

            if (!empty($inr_result && $cur_result)) {
                $inr_total = 0;
                $cur_total = 0;
                foreach ($inr_result as $data) {
                    $currency_amount = "{$fromCurrency}_amount";
                    $inr_total += $data->$currency_amount;
                }
                foreach ($cur_result as $data) {
                    $currency_amount = "{$toCurrency}_amount";
                    $cur_total += $data->$currency_amount;
                }
                if ($request->convert_type == 'purchase') {
                    $float_avg = $inr_total / $cur_total;
                    $avg = number_format($float_avg, 2);
                    $purchase_avg = array(
                        'inr_total' => $inr_total,
                        'cur_total' => $cur_total,
                        'avg' => $avg
                    );
                } else {
                    $float_avg = $cur_total / $inr_total;
                    $avg = number_format($float_avg, 2);
                    $purchase_avg = array(
                        'cur_total' => $inr_total,
                        'inr_total' => $cur_total,
                        'avg' => $avg
                    );
                }
                return response()->json($purchase_avg);
            }
        }
    }
}
