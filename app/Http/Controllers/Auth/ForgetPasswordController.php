<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordVerificationNotification;
use Illuminate\Http\Request;

class ForgetPasswordController extends Controller
{
    //forget password
    public function forgetPassword(ForgetPasswordRequest $request)
    {
        $input = $request->only('email');
        $user = User::where('email', $input)->first();
        $user->notify(new ResetPasswordVerificationNotification());
        return response()->Json(['success' => true], 200);
    }
}
