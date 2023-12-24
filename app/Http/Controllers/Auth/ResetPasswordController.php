<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    private $otp;

    public function __construct()
    {
        $this->otp = new Otp;
    }

    // Verification Code
    public function verificationCode(ResetPasswordRequest $request)
    {
        $otp2 = $this->otp->validate($request->email, $request->otp);
        if (!$otp2->status) {
            return response()->Json(['success' => false, "message" => "Invalid code"], 400);
        }
        $user = User::where('email', $request->email)->first();
        $token = $user->createToken($user->name);

        return response()->Json(['success' => true, 'token' => $token->plainTextToken], 200);
    }

    // reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ]);

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        $user->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->Json(['success' => true, "message" => "Success updating password"], 200);
    }

    // reset password with old password
    public function resetPasswordWithOldPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'old_password' => ['required', 'min:8'],
            'new_password' => ['required', 'min:8'],
        ]);

        $user = User::where('email', $request->email)->first();
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->Json(['success' => false, "message" => "Invalid old password"], 400);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        $user->tokens->each(function ($token) {
            $token->delete();
        });

        $token =  $user->createToken($user->name);

        return response()->Json(['success' => true, "token" => $token, "message" => "Success updating password"], 200);
    }
}
