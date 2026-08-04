<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User;

class NewUserRegistrationNotification extends Notification
{
    use Queueable;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type'    => 'new_registration',
            'user_id' => $this->user->id,
            'name'    => $this->user->name,
            'message' => 'New resident registration pending approval: ' . $this->user->name . '.',
            'link'    => '/admin/account-approvals',
        ];
    }
}
