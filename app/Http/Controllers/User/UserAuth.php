<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserAuth extends Controller
{
    private $otp;

    public function __construct()
    {
        $this->otp = new Otp;
    }


    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'name' => 'required',
            'password' => 'required',
        ]);

        $userOld = User::where('email', $request->email)->first();
        if ($userOld) {
            return response()->json([
                "success" => false,
                'message' => 'The account already exists.',
            ], 400);
        }

        $user = new User();
        $user->email = $request->email;
        $user->name = $request->name;
        $user->location = null;
        $user->image = 'default_image.jpg';
        $user->password = bcrypt($request->password);

        $role = new Role();
        $role->role = 'user';
        $user->save();
        $user->role()->save($role);

        return response()->json([
            'success' => true,
            'message' => 'The account has been created, you will receive a confirmation code',
        ]);
    }

    public function sendCode(Request $request)
    {
        $request->validate([
            'email' => 'required',
        ]);

        $user = new User();
        $user->email = $request->email;
        $user->notify(new EmailVerificationNotification());

        return response()->json([
            'success' => true,
            'message' => 'The code has been sent',
        ]);
    }

    public function verificationCode(Request $request)
    {
        $request->validate([
            'otp' => 'required',
            'email' => 'required',
        ]);

        $otp2 = $this->otp->validate($request->email, $request->otp);
        if (!$otp2->status) {
            return response()->Json(['success' => false, "message" => "Invalid code"], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->email_verified_at = now();
        $user->save();

        return response()->json([
            'message' => 'code is true',
            'success' => true
        ]);
    }

    // login user
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->role->role !== 'user') {
            return response()->json([
                'message' => 'You are not an user.',
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->Json(["error" => 'username or password is incorrect']);
        }

        if ($user->email_verified_at === null) {
            return response()->json([
                'message' => 'Please confirm the account.',
            ], 401);
        }

        $token = $user->createToken($user->name);
        return response()->Json(["token" => $token->plainTextToken, 'user' => $user]);
    }

    // edit a profile
    public function editProfile(Request $request, $id)
    {
        $request->validate([
            'image' => 'nullable',
            'name' => 'required',
        ]);

        $user = User::findOrFail($id);

        if ($user->id != auth()->user()->id) {
            return response()->json([
                'message' => 'You do not have permission to edit',
            ], 401);
        }

        if ($request->file('image')) {
            $filename = Str::random(32) . "." . $request->image->getClientOriginalExtension();
            $request->image->move('uploads/', $filename);
            $user->image = $filename;
        }

        $user->name = $request->name;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'updated successfully',
        ], 201);
    }

    // edit a profile
    public function editLocation(Request $request, $id)
    {
        $request->validate([
            'location' => 'nullable',
        ]);

        $user = User::findOrFail($id);

        if ($user->id != auth()->user()->id) {
            return response()->json([
                'message' => 'You do not have permission to edit',
            ], 401);
        }

        $user->location = $request->location;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'location updated successfully',
        ], 201);
    }
}
