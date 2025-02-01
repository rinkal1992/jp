<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Redirect;

class AuthController extends Controller
{
    public function login()
    {
        return view('Admin/login');
    }

    public function login_data(Request $req)
    {
        $validated = $req->validate([
            'user_name' => 'required',
            'password' => 'required'
        ]);
        if ($validated) {
            if (Auth::attempt(['user_name' => $req->user_name, 'password' => $req->password])) {
                return redirect('/party_list');
            } else {
                return back()->with('fail', 'Wrong credential');
            }
        }
    }

    public function change_password_form()
    {
        return view('Admin/change_password');
    }

    public function change_password(Request $req)
    {
        $rules = array(
            'password' => 'required',
            'npassword' => 'required',
            'cpassword' => 'required'
        );
        $validated = Validator::make($req->all(), $rules);
        if ($validated->fails()) {
            return response()->json(['error' => $validated->errors()->toArray()]);
        } else {
            $data = DB::table('users');
            $data->where('id', Auth::User()->id);
            $result_array = $data->first();
            if (!empty($result_array)) {
                if (Hash::check($req->password, $result_array->password)) {
                    if ($req->npassword == $req->cpassword) {
                        if (Hash::check($req->npassword, $result_array->password)) {
                            $msg = ['st' => 'failed', 'msg' => 'Old and New password is same'];
                        } else {
                            $data->update(array('password' => Hash::make($req->npassword)));
                            $msg = ['st' => 'success', 'msg' => 'Password Changed'];
                        }
                    } else {
                        $msg = ['st' => 'failed', 'msg' => 'Password not match'];
                    }
                } else {
                    $msg = ['st' => 'failed', 'msg' => 'Old password is wrong'];
                }
            }
            return response()->json($msg);
        }
    }

    public function logout(Request $req)
    {
        Session::flush();
        Auth::logout();

        $req->session()->invalidate();
        $req->session()->regenerateToken();

        return redirect('/');
    }
}
