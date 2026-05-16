<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticationController extends Controller
{
    public function login(LoginRequest $request)
    {
        $loginRequest = $request->validated();
        $user = User::where('email', $loginRequest['email'])->first();
        if(!$user || !Hash::check($loginRequest['password'], $user->password) ){
//            Login gagal
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            "message"=>"login berhasil",
            "token" => $token,
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "fullname" => $user->fullname,
                "profile" => $user->profile,
            ]
        ]);
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json([
            "message" => "logout berhasil"
        ]);
    }
}
