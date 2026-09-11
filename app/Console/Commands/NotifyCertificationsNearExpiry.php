<?php

namespace App\Console\Commands;

use App\Enums\CertificationVerificationStatus;
use App\Models\EmployeeCertification;
use App\Notifications\CertificationNearExpiry;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class NotifyCertificationsNearExpiry extends Command
{
    protected $signature = 'notification:certifications-near-expiry
        {--days=60 : Number of days before expiry to flag a certification}';

    protected $description = 'Notify employees (and HR) about certifications expiring soon';

    public function handle(NotificationService $notifications): int
    {
        $threshold = now()->addDays((int) $this->option('days'));

        $expiring = EmployeeCertification::query()
            ->where('verification_status', CertificationVerificationStatus::Verified->value)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now()->toDateString(), $threshold->toDateString()])
            ->with(['employee.user', 'certification'])
            ->get();

        $count = 0;

        foreach ($expiring as $certification) {
            $user = $certification->employee?->user;

            if ($user !== null) {
                $user->notify(new CertificationNearExpiry($certification));
                $count++;
            }
        }

        $this->info("Notified {$count} employee(s) about expiring certifications.");

        return self::SUCCESS;
    }
}
