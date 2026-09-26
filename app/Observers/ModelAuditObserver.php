<?php

namespace App\Observers;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ModelAuditObserver
{
    public function created(Model $model): void
    {
        $attributes = $this->sanitize($model, $model->getAttributes());

        app(AuditService::class)->log(
            $this->event($model, 'created'),
            $model,
            null,
            $attributes
        );
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();

        $old = collect($changes)
            ->mapWithKeys(fn (mixed $value, string $key): array => [$key => $model->getOriginal($key)])
            ->all();

        $changes = $this->sanitize($model, $changes);
        $old = $this->sanitize($model, $old);

        if (empty($changes)) {
            return;
        }

        app(AuditService::class)->log(
            $this->event($model, 'updated'),
            $model,
            $old,
            $changes
        );
    }

    public function deleted(Model $model): void
    {
        $attributes = $this->sanitize($model, $model->getAttributes());

        app(AuditService::class)->log(
            $this->event($model, 'deleted'),
            $model,
            $attributes,
            null
        );
    }

    protected function event(Model $model, string $action): string
    {
        $entity = Str::snake(class_basename($model));

        return "{$entity}.{$action}";
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function sanitize(Model $model, array $data): array
    {
        $hidden = array_flip($model->getHidden());

        return array_diff_key($data, $hidden);
    }
}
