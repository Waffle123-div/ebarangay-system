<?php

namespace App\Notifications;

use App\Models\CommunityConcern;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConcernStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected CommunityConcern $concern,
        protected string $message
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'       => 'concern_update',
            'concern_id' => $this->concern->concern_id,
            'title'      => $this->concern->title,
            'status'     => $this->concern->status,
            'message'    => $this->message,
            'link'       => '/resident/community-concerns',
        ];
    }
}
