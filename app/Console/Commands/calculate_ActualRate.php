<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\MasterController;
use App\Models\Utils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class calculate_ActualRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate_ActualRate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'calculate wehre entries 0';

    /**
     * Create a new command instance.
     *
     * @return void
     */


    private $utils;

    public function __construct()
    {
        parent::__construct();
        $this->utils = new Utils();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $types = config('global.transction_type_list');
//        $final_result = array();
        foreach ($types as $type) {
            $builder = DB::table("transaction_$type");
            $builder->select('*');
            $builder->where('info', '=', 0)->where('info', '!=', 5);
            $result = $builder->get()->toArray();
//            $final_result[$type] = $result;

            //        }
//            echo "<pre>";print_r($result);exit;
//        $counter = 1;
            foreach ($result as $item) {

                $note = $item->note;
//                $pattern = '/exch\s+(\w+)_(\w+)\s+(\d+)\s*x\s*([\d.]+)\s*=\s*(\d+)/';
//
//                if (preg_match($pattern, $note, $matches)) {
//                    echo "<pre>";print_r($matches);exit;
//                    $from_currency = $matches[1];
//                    $to_currency = $matches[2];
//                    $from_amount = $matches[3];
//                    $exchange_rate = $matches[4];
//                    $to_amount = $matches[5];
//                } else {
//                    echo "Pattern did not match.\n"; // Debug statement
//                }
                $Data = explode(' ', $note);
                if ($Data[0] == 'exch' && count($Data) == 7) {
                    $splitData = explode('_', $Data[1]);
                    $from_currency = $splitData[0];
                    $to_currency = $splitData[1];

                    $from_amount = $Data['2'];
                    $to_amount = $Data['6'];
                    $ConversionInfo = $this->utils->getConversionInfo($from_currency, $to_currency, $from_amount, $to_amount);
                    if ($ConversionInfo['actual_rate'] == 0.04) {
                        $ConversionInfo['actual_rate'] = round($from_amount / $to_amount, 2);
                    }
//                    if ($ConversionInfo['actual_rate'] == 0.04) {
//                        $ConversionInfo['actual_rate'] = round($to_amount / $from_amount, 2);
//                    }
//                    if ($ConversionInfo['actual_rate'] == 0.01) {
//                        $ConversionInfo['actual_rate'] = round($from_amount / $to_amount, 2);
//                    }
//                    if ($ConversionInfo['actual_rate'] == 0.27) {
//                        $ConversionInfo['actual_rate'] = round($from_amount / $to_amount, 2);
//                    }
//                echo "<pre>";print_r($ConversionInfo);exit;
                    $update_builder = DB::table("transaction_$type");

                    $update_builder->where('srn', $item->srn)->update(['actual_rate' => $ConversionInfo['actual_rate']]);
                }
            }
            echo "first Actual Rate Calculated in $type \n";
        }
    }
}
