<?php

namespace App\Notifications;

use App\Models\ProfileChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProfileChangeRequested extends Notification
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
            'employee_id' => $this->request->employee_id,
            'employee_name' => $this->request->employee?->full_name,
            'type' => $this->request->type->value,
            'subject' => $this->request->subjectDisplay(),
            'url' => "/admin/profile-change-requests/{$this->request->id}",
        ];
    }
}
