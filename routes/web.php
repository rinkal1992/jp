<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Middleware\Authenticate;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route::get('/', function () {
//     return redirect('/login');
// });
Route::controller(AuthController::class)->group(function () {
    Route::get('/', 'login');
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'login_data');
    Route::get('/change_password_form', 'change_password_form')->name('change_password_form');
    Route::post('change_password', 'change_password');
    Route::get('/logout', 'logout');
});
Route::middleware([Authenticate::class])->group(function () {
    Route::controller(MasterController::class)->group(function () {
        Route::get('/party_list', 'party_list')->name('party_list');
        Route::post('/add_party', 'add_party');

     	Route::post('/add_group', 'add_group');
        Route::post('/getGroups', 'getGroups');
        Route::get('/group_wise_data', 'group_wise_data');
      
        Route::post('/getDrparty', 'getDrparty');
        Route::post('/getCrparty', 'getCrparty');
        Route::post('/getUser', 'getUser');
        Route::post('/getAmount', 'getAmount');

        Route::get('/transaction/{type}', 'transaction');
        Route::post('/submit_transaction', 'submit_transaction');

        Route::get('/report_list', 'report_list')->name('report_list');
        Route::get('/statement_list', 'statement_list')->name('statement_list');
        Route::get('/all_statement_list', 'all_statement_list')->name('all_statement_list');
        Route::get('/party_report', 'party_report')->name('party_report');

        Route::post('/getExchTotal', 'getExchTotal');
        Route::get('/exchange_currency', 'exchange_currency')->name('exchange_currency');
        Route::post('/submit_exchange_currency', 'submit_exchange_currency');

        Route::get('/convert/{convert_type}/{type}', 'convert');
        Route::post('/submit_convert', 'submit_convert');
        Route::post('/getpurchaseavg', 'getpurchaseavg');

        Route::get('/export_party_bal', 'exportBal')->name('export_party_bal');
        Route::get('/all_cur_statement_pdf', 'all_statementPdf')->name('all_cur_statement_pdf');
        Route::get('/statement_pdf', 'statementPdf')->name('statement_pdf');
        Route::get('/statement_excel', 'exportStatementExcel')->name('statement_excel');
        Route::get('/all_currency_horizontal', 'all_currency_horizontal')->name('all_currency_horizontal');

        Route::get('/commission_transfer', 'commission_transfer');
        Route::get('/statement_checkup', 'statement_checkup')->name('statement_checkup');

        Route::post('/getPartyBal', 'getPartyBal');

        Route::post('/delete_entry', 'delete_entry');
    });
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
