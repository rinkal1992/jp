<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\MasterController;
use App\Models\Utils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class calculate_ActualRate_USD extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate_ActualRate_USD';

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
        $builder = DB::table("transaction_usd");
        $builder->select('*');
        $builder->where('info', '=', 0)->where('info', '!=', 5);
        $result = $builder->get()->toArray();
        foreach ($result as $item) {
            $note = $item->note;
            $Data = explode(' ', $note);
            if ($Data[0] == 'exch' && count($Data) == 5) {
                $splitData = explode('_', $Data[1]);
                $from_currency = $splitData[0];
                $to_currency = $splitData[1];

                $SameNote = DB::table("transaction_$to_currency")->select('*')->where('note', '=', $note)->first();
//                echo "<pre>";print_r($SameNote);exit;
                $from_amount = $item->amount;
                $to_amount = $SameNote->amount;

                $ConversionInfo = $this->utils->getConversionInfo($from_currency, $to_currency, $from_amount, $to_amount);
                if ($ConversionInfo['actual_rate'] == 0.27) {
//                    echo "<pre>";print_r($item);exit;
//                    rount($item->amount / $Data[2], 2);
                    $ConversionInfo['actual_rate'] = round($Data[2] / $item->amount, 2);
                }
                if ($ConversionInfo['actual_rate'] == 1.00) {
                    $ConversionInfo['actual_rate'] = round($Data[2] / $item->amount, 2);
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
                $update_builder = DB::table("transaction_usd");

                $update_builder->where('srn', $item->srn)->update(['actual_rate' => $ConversionInfo['actual_rate']]);
            }
        }
        echo "Actual Rate Calculated in USD \n";
    }
}
