<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    //login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->role->role !== 'admin') {
            return response()->json([
                'message' => 'You are not an admin'
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->Json(["error" => 'username or password is incorrect'], 400);
        }

        $token = $user->createToken($user->name);
        return response()->Json(["token" => $token->plainTextToken, 'user' => $user], 200);
    }
}
