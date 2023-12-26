<?php

namespace App\Http\Controllers;

use App\Models\Request as ModelsRequest;
use App\Models\User;
use App\Notifications\SendRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class RequestController extends Controller
{
    //


    use ApiResponseTrait;

    //This is simulation for sending any request just to get notifications
    // when integrate we will replace it with the real requests made
    public function sendRequest(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'body' => 'required'
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponse(null, $validator->errors(), 400);
        }

        $dataRequest = ModelsRequest::create([
            'user_id' => 3, //when integrate we will replace it with auth->user which logged in 
            'title' => $request->title,
            'body' => $request->body
        ]);


        $user_send = "user1"; //when integrate we will replace it with auth->user->name which logged in 

        //here we send a notfication to only admins
        $admins = User::whereHas('role', function ($query) {
            $query->where('role', 'admin');
        })->get();


        Notification::send($admins, new SendRequest($dataRequest->id, $user_send, $dataRequest->title));
        return $this->apiResponse(null, 'Request sent successfully', 200);
    }

    public function showAllNotification()
    {
        $notifications = DB::table('notifications')->get();

        if ($notifications->isEmpty()) {
            return $this->apiResponse(null, 'No Notifications retrieved', 400);
        }

        return $this->apiResponse($notifications, 'Notifications retrieved successfully', 200);
    }
    public function showNotification($id)
    {
        $requestData = ModelsRequest::find($id);

        if (!$requestData) {
            return $this->apiResponse(null, 'No Notification retrived', 400);
        }
        $getId = DB::table('notifications')->where('data->request_id', $id)->pluck('id');
        DB::table('notifications')->where('id', $getId)->update(['read_at' => now()]);
        return $this->apiResponse($requestData, 'Notification retrived successfully', 200);
    }

    public function markAsRead()
    {
        $user = User::find(1); //when integrate we will replace it with admin id

        foreach ($user->unreadNotifications as $notification) {
            $notification->markAsRead();
        }
        return $this->apiResponse(null, 'Notifications marked as read successfully', 200);
    }
}
