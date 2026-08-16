<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Support\EnumLabels;
use Filament\Widgets\ChartWidget;

class ProjectsByStatusChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 2;

    public function getHeading(): string
    {
        return 'Projects by Status';
    }

    public function getDescription(): string
    {
        return 'Distribution of projects across all lifecycle statuses.';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Project::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->pluck('total', 'status');

        return [
            'labels' => $counts
                ->map(fn (int $total, string $status): string => EnumLabels::friendly(ProjectStatus::from($status)))
                ->values()
                ->all(),
            'datasets' => [
                [
                    'data' => $counts->values()->all(),
                    'backgroundColor' => [
                        '#94a3b8',
                        '#38bdf8',
                        '#f59e0b',
                        '#facc15',
                        '#6366f1',
                        '#22c55e',
                        '#ef4444',
                        '#a855f7',
                    ],
                ],
            ],
        ];
    }
}
