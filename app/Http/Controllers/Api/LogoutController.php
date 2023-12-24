<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    //
    public function logout(Request $request)
    {
        $request->validate([
            "email" => "required"
        ]);
        $user = User::where('email', $request->email)->first();
        $user->tokens->each(function ($token) {
            $token->delete();
        });
        return response()->Json(['success' => true, "message" => "Success logout"], 200);
    }
}
