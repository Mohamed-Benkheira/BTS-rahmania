<?php

namespace App\Notifications;

use App\Models\EmployeeCertification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CertificationNearExpiry extends Notification
{
    use Queueable;

    public function __construct(public EmployeeCertification $certification) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'certification_name' => $this->certification->certification?->name,
            'expires_at' => $this->certification->expires_at?->toDateString(),
            'url' => '/portal/my-requests',
        ];
    }
}
