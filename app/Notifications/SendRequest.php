<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendRequest extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

     private $request_id;
     private $user_send;

     private $title;


     
    public function __construct($request_id,$user_send,$title)
    {
        //
        $this->request_id=$request_id;
        $this->user_send=$user_send;
        $this->title=$title;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id'=>$this->request_id,
            'user_send'=>$this->user_send,
            'title'=>$this->title
        ];
    }
}
