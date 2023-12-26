<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    //

    use ApiResponseTrait;


    public function messagesCenter(Request $request)
    {
        $adminID = 1; //when integrate we will replace it with admin id 
        $userID = 3; //when integrate we will replace it with auth->user which logged in 
    
        $conversation = Message::whereIn('sender_id', [$adminID, $userID])
            ->whereIn('receiver_id', [$adminID, $userID])
            ->orderBy('created_at')
            ->get();
        if(!$conversation)
        {
            return $this->apiResponse(null,'Start sending msg',200);

        }
        return $this->apiResponse($conversation,'Conversation retrived successfully',200);

        
    }

    public function sendMessageByuser(Request $request)
    {
        $validator= Validator::make($request->all(),[
            'message_body'=>'required',
        ]
        );
        if($validator->fails())
        {
            return $this->apiResponse(null,$validator->errors(),400);
    
        }
        $userID = 3; //when integrate we will replace it with auth->user which logged in 
        
        $message = new Message();
        $message->sender_id = $userID;
        $message->receiver_id = 1; //when integrate we will replace it with admin id 
        $message->message_body = $request->message_body;
        $message->save();
        return $this->apiResponse($message->message_body,'Reply sent successfully!',200);

    }


    public function adminMessagesCenter()
    {
        $adminID = 1;//when integrate we will replace it with admin id

        $latestMessages = Message::select('id', 'sender_id', 'receiver_id', 'message_body', 'created_at')
        ->where('receiver_id', $adminID)
        ->latest('created_at')
        ->get();
        $adminMessages = collect();
        $latestMessages->each(function ($message) use (&$adminMessages) {
            if (!$adminMessages->has($message->sender_id)) {
                $adminMessages->put($message->sender_id, $message);
            }
        });
        if(!$latestMessages)
        {
            return $this->apiResponse(null,"Customers hadn't start any conversation yet'",200);

        }
        return $this->apiResponse($adminMessages,'Conversations retrived successfully',200);

    }

    public function showConversation($userId)
    {
        $adminID = 1; //when integrate we will replace it with admin id
        $conversation = Message::where(function ($query) use ($adminID, $userId) {
            $query->where('receiver_id', $adminID)
                ->where('sender_id', $userId);
        })->orWhere(function ($query) use ($adminID, $userId) {
            $query->where('sender_id', $adminID)
                ->where('receiver_id', $userId);
        })->orderBy('created_at')
            ->get();
            return $this->apiResponse($conversation,'Conversation retrived successfully',200);

    }

    public function sendReply(Request $request, $userId)
    {
        $validator= Validator::make($request->all(),[
            'message_body'=>'required',
        ]
        );
        if($validator->fails())
        {
            return $this->apiResponse(null,$validator->errors(),400);
    
        }
        $adminID = 1;
        
        $message = new Message();
        $message->sender_id = $adminID;
        $message->receiver_id = $userId;
        $message->message_body = $request->message_body;
        $message->save();

        return $this->apiResponse($message->message_body,'Reply sent successfully!',200);
    }

    public function deleteMessage($id)
    {
        $message = Message::find($id);
        if(!$message)
        {
            return $this->apiResponse(null,'No message found',400);

        }
        $message->delete();
        return $this->apiResponse(null,'Message deleted successfully!',200);

    }

/*     public function editMessage($id)
    {        
        $message = Message::find($id);
        return Response::json(['message' => $message], 200);
    }
 */
    public function editMessageSend(Request $request ,$id)
    {
        $validator= Validator::make($request->all(),[
            'message_body'=>'required',
        ]
        );
        if($validator->fails())
        {
            return $this->apiResponse(null,$validator->errors(),400);
    
        }
        $editedMessageBody = $request->message_body;
        $message = Message::find($id);
        if(!$message)
        {
            return $this->apiResponse(null,'No message found',400);

        }
        $message->message_body = $editedMessageBody;
        $message->save();
        return $this->apiResponse($editedMessageBody,'Message edited successfully',400);

    }
    public function sendPhotoByAdmin(Request $request,$userId)
       {
        $validator= Validator::make($request->all(),[
            'media' => 'required|file|mimes:jpeg,jpg,png,mp4,avi,mov', 
            ]
        );
        if($validator->fails())
        {
            return $this->apiResponse(null,$validator->errors(),400);
    
        }
        $image=$request->file('media')->getClientOriginalName();
        $path=$request->file('media')->storeAs('media',$image,'public');
        $adminID = 1; //when integrate we will replace it with admin id 
        
        $message = new Message();
        $message->sender_id = $adminID;
        $message->receiver_id = $userId;
        $message->message_body = $path;
        $message->photo=1;
        $message->save();
        return $this->apiResponse($image,'Image send successfully ',200);

       }
       public function sendPhotoByUser(Request $request)
       {
        $validator= Validator::make($request->all(),[
            'media' => 'required|file|mimes:jpeg,jpg,png,mp4,avi,mov', 
        ]
        );
        if($validator->fails())
        {
            return $this->apiResponse(null,$validator->errors(),400);
    
        }
        $image=$request->file('media')->getClientOriginalName();
        $path=$request->file('media')->storeAs('media',$image,'public');
        $adminID = 1;
        $message = new Message();
        $message->sender_id = 3; //when integrate we will replace it with auth->user which logged in 
        $message->receiver_id = $adminID;
        $message->message_body = $path;
        $message->photo=1;
        $message->save();
        return $this->apiResponse($image,'Image send successfully ',200);
       }
       public function sendVoiceByAdmin(Request $request,$userId)
       {
        $validator= Validator::make($request->all(),[
            'voice'=>'required|file|mimes:audio/mpeg,mpga,mp3,wav',
        ]
        );
        if($validator->fails())
        {
            return $this->apiResponse(null,$validator->errors(),400);
    
        }
        $voice=$request->file('voice')->getClientOriginalName();
        $path=$request->file('voice')->storeAs('voices',$voice,'public');
        $adminID = 1;//when integrate we will replace it with admin id 
        
        $message = new Message();
        $message->sender_id = $adminID;
        $message->receiver_id = $userId;
        $message->message_body = $path;
        $message->voice=1;
        $message->save();
        return $this->apiResponse($voice,'Voice send successfully ',200);
       }
       public function sendVoiceByUser(Request $request)
       {
        $validator= Validator::make($request->all(),[
            'voice'=>'required|file|mimes:audio/mpeg,mpga,mp3,wav',
        ]
        );
        if($validator->fails())
        {
            return $this->apiResponse(null,$validator->errors(),400);
    
        }
        $voice=$request->file('voice')->getClientOriginalName();
        $path=$request->file('voice')->storeAs('voices',$voice,'public');
        $adminID = 1;
        $message = new Message();
        $message->sender_id =  3; //when integrate we will replace it with auth->user which logged in 
        $message->receiver_id = $adminID;
        $message->message_body = $path;
        $message->voice=1;
        $message->save();
        return $this->apiResponse($voice,'Voice send successfully ',200);
       }
}
