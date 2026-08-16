<?php

namespace App\Observers\Concerns;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

trait AuditsChanges
{
    public function created(Model $model): void
    {
        app(AuditService::class)->log($this->event('created'), $model, null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();

        $old = collect($changes)
            ->mapWithKeys(fn (mixed $value, string $key): array => [$key => $model->getOriginal($key)])
            ->all();

        app(AuditService::class)->log($this->event('updated'), $model, $old, $changes);
    }

    public function deleted(Model $model): void
    {
        app(AuditService::class)->log($this->event('deleted'), $model, $model->getAttributes(), null);
    }

    abstract protected function event(string $action): string;
}
