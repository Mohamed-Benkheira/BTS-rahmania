<?php

namespace App\Notifications;

use App\Models\ProfileChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProfileChangeResolved extends Notification
{
    use Queueable;

    public function __construct(public ProfileChangeRequest $request) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'type' => $this->request->type->value,
            'status' => $this->request->status->value,
            'reviewer_note' => $this->request->reviewer_note,
            'url' => '/portal/my-requests',
        ];
    }
}
