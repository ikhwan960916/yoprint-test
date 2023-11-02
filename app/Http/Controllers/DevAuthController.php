<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DevAuthController extends Controller
{
    public function showUserLogin()
    {
        if (Auth::check()){
            return redirect()->route('product.upload-csv.view');
        }
        $users = User::all();
        return view('dev.user-login-list', compact('users'));
    }

    public function login($user_id)
    {
        $user = User::findOrFail($user_id);

        Auth::login($user);

        return redirect()->route('product.upload-csv.view');
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('auth.login.view');
    }
}
