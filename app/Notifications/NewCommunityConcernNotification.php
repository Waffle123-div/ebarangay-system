<?php

namespace App\Notifications;

use App\Models\CommunityConcern;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommunityConcernNotification extends Notification
{
    use Queueable;

    public function __construct(protected CommunityConcern $concern)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'         => 'new_concern',
            'concern_id'   => $this->concern->concern_id,
            'title'        => $this->concern->title,
            'category'     => $this->concern->category,
            'resident_name'=> $this->concern->resident_name,
            'message'      => 'New Community Concern Submitted: ' . $this->concern->title . ' from ' . $this->concern->resident_name . '.',
            'link'         => '/admin/concerns',
        ];
    }
}
