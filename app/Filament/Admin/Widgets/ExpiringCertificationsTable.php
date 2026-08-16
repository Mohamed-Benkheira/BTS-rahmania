<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\CertificationVerificationStatus;
use App\Models\EmployeeCertification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Carbon;

class ExpiringCertificationsTable extends TableWidget
{
    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Certifications Expiring Soon')
            ->description('Verified certifications expiring within 60 days or already expired.')
            ->query(
                EmployeeCertification::query()
                    ->where('verification_status', CertificationVerificationStatus::Verified->value)
                    ->whereNotNull('expires_at')
                    ->whereDate('expires_at', '<=', Carbon::today()->addDays(60))
                    ->with(['employee', 'certification'])
                    ->orderBy('expires_at')
                    ->limit(10),
            )
            ->columns([
                TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->getStateUsing(fn (EmployeeCertification $record): string => $record->employee?->full_name ?? '—')
                    ->tooltip('The employee who holds this certification.'),
                TextColumn::make('certification.name')
                    ->label('Certification')
                    ->tooltip('The certification that is expiring.'),
                TextColumn::make('issued_at')
                    ->label('Issued')
                    ->date()
                    ->tooltip('The date the certification was issued.'),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date()
                    ->color(fn (EmployeeCertification $record): string => $this->daysLeft($record) < 0 ? 'danger' : ($this->daysLeft($record) <= 30 ? 'warning' : 'info'))
                    ->tooltip(fn (EmployeeCertification $record): string => $this->daysLeft($record) < 0
                        ? 'This certification has already expired.'
                        : "Expires in {$this->daysLeft($record)} days."),
                TextColumn::make('expires_at')
                    ->label('Days Left')
                    ->getStateUsing(fn (EmployeeCertification $record): string => $this->daysLeft($record) < 0 ? 'Expired' : "{$this->daysLeft($record)}d")
                    ->badge()
                    ->color(fn (EmployeeCertification $record): string => $this->daysLeft($record) < 0 ? 'danger' : ($this->daysLeft($record) <= 30 ? 'warning' : 'info'))
                    ->tooltip('Negative days mean the certification is already expired.'),
            ])
            ->paginated(false);
    }

    private function daysLeft(EmployeeCertification $record): int
    {
        return (int) Carbon::today()->diffInDays($record->expires_at, false);
    }
}
