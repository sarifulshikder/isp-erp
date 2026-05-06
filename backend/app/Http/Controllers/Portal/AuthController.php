<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('portal.dashboard');
        }
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $customer = \App\Models\Customer::where('username', $request->username)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return back()->withErrors(['username' => 'Username বা Password ভুল।'])->withInput();
        }

        if ($customer->status === 'suspended') {
            return back()->withErrors(['username' => 'আপনার সংযোগ স্থগিত আছে। অফিসে যোগাযোগ করুন।'])->withInput();
        }

        Auth::guard('customer')->login($customer, $request->boolean('remember'));

        return redirect()->route('portal.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.login');
    }
}
