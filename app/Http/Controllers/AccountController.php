<?php

namespace App\Http\Controllers;

use App\User;

use Illuminate\Http\Request;
use Auth;

class AccountController extends Controller {
    public function index() {
        /**
         *  Check whether user is logged in
         *  if logged in redirect to dashboard
         *  Otherwise redirect to index / login page
         */
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('index');
    }

    public function login(Request $request) {
        /**
         *  Attempt to login with the credentials
         *  remember_token to automatically login next time
         *  if wrong credentials redirect to index
         */
        if (Auth::attempt($request->only('email', 'password'), 1)) {   
            return redirect()->route('dashboard');
        }

        return redirect()->route('index')->withErrors([
            'error' => 'Incorrect Email and/or Password.',
        ]);
    }

    public function signup(Request $request) {
        /**
         *  check if request include latitude and longitude
         *  redirect if not
         */
        if (!isset($request->latitude) || !isset($request->longitude)) {
            return redirect()->route('index', ['signup' => true])->withErrors([
                'error' => 'Sorry, we could not fetch your location data.'
            ]);
        }

        /**
         *  Check if an user account already exist with same email
         *  redirect back if true
         *  else create a new user with the data
         *  and redirect back to login
         */
        $duplicateEmail = User::where('email', $request->email)->count();
        if ($duplicateEmail > 0) {
            return redirect()->route('index', ['signup' => true])->withErrors([
                'error' => 'An user account with the given email address already exists.'
            ]);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'dob' => $request->dob,
            'gender' => $request->gender
        ]);

        return redirect()->route('index');
    }

    public function logout() {
        Auth::logout();
        return redirect()->route('index');
    }
}