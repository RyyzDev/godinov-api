<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Illuminate\Validation\ValidationException;



class LoginController extends Controller
{

    public function authLogin(Request $request){
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        //if ($credentials){}

        $user = User::where('email', $request->email)->first();
       	if (! $user || ! Hash::check($request->password, $user->password)) {
       		throw ValidationException::withMessages(['email' => ['Akun tidak Ditemukan!'],]);
       	}
       	return $user->createToken('token login')->plainTextToken;
       }

    public function logout(Request $request){
		$request->user()->currentAccessToken()->delete();
    $message = "Berhasil Logout";
      return response()->json([
          'message' => $message,
          //'data' => $inbox
      ], 200);
	}

	public function currentUser(Request $request){
		return response()->json(Auth::user());
	}



}
