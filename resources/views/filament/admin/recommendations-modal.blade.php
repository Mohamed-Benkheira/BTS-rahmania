<style>
    .recommendation-list {
        --rvc-card: #ffffff;
        --rvc-soft: #f8fafc;
        --rvc-border: #e4e9f0;
        --rvc-track: #e7ebf1;
        --rvc-text: #0f172a;
        --rvc-muted: #5b6577;
        --rvc-dim: #94a3b8;
        --rvc-good: #059669;
        --rvc-mid: #d97706;
        --rvc-low: #e11d48;
        --rvc-soft-good: #ecf9f3;
        --rvc-soft-mid: #fdf6e9;
        --rvc-soft-low: #fdeef2;
        --rvc-rank-bg: #e9edf3;
        --rvc-rank-text: #4b5a75;
        --rvc-pill-pending-bg: #f1f4f8;
        --rvc-pill-pending-text: #506080;
        --rvc-pill-approved-bg: #def4ea;
        --rvc-pill-approved-text: #0b7a52;
        --rvc-pill-rejected-bg: #fbe4e8;
        --rvc-pill-rejected-text: #c14a5e;
    }

    .dark .recommendation-list {
        --rvc-card: #1a2332;
        --rvc-soft: #131b27;
        --rvc-border: #2a3548;
        --rvc-track: #2e3a50;
        --rvc-text: #e6ebf2;
        --rvc-muted: #97a5b7;
        --rvc-dim: #63728a;
        --rvc-good: #34d399;
        --rvc-mid: #fbbf24;
        --rvc-low: #fb7185;
        --rvc-soft-good: #072118;
        --rvc-soft-mid: #28200c;
        --rvc-soft-low: #2f131c;
        --rvc-rank-bg: #2d394d;
        --rvc-rank-text: #c3cedd;
        --rvc-pill-pending-bg: #253041;
        --rvc-pill-pending-text: #a9b6c6;
        --rvc-pill-approved-bg: #0d3a2a;
        --rvc-pill-approved-text: #5fe0a8;
        --rvc-pill-rejected-bg: #3f1b25;
        --rvc-pill-rejected-text: #fda4af;
    }
</style>

<div class="recommendation-list" style="display: flex; flex-direction: column; gap: 12px;">
    @forelse ($recommendations as $rec)
        @php
            $rank = (int) $rec->rank;
            $name = $rec->employee?->full_name ?? ($rec->team?->name ?? ($rec->department?->name ?? 'N/A'));
            $subtitle = $rec->employee_id
                ? ($rec->employee?->employee_code ?? '')
                : ($rec->team_id
                    ? 'Team · '.($rec->team?->code ?? '')
                    : 'Department · '.($rec->department?->code ?? ''));

            $score = (float) ($rec->total_score ?? 0);
            $pct = round($score * 100, 1);

            $ex = $rec->explanation ?? [];
            $skills = $ex['skills'] ?? ['matched' => 0, 'total' => 0];
            $certs = $ex['certifications'] ?? ['matched' => 0, 'total' => 0];
            $langs = $ex['languages'] ?? ['matched' => 0, 'total' => 0];
            $avail = (float) ($ex['availability'] ?? 0);
            $workload = (float) ($ex['workload'] ?? 0);

            $skillPct = $skills['total'] > 0 ? round(($skills['matched'] / $skills['total']) * 100) : 0;
            $certPct = $certs['total'] > 0 ? round(($certs['matched'] / $certs['total']) * 100) : 0;
            $langPct = $langs['total'] > 0 ? round(($langs['matched'] / $langs['total']) * 100) : 0;
            $availPct = max(0, min(100, (int) round($avail * 100)));
            $usedPct = max(0, min(100, (int) round((1 - $workload) * 100)));

            $level = match (true) {
                $score >= 0.8 => 'good',
                $score >= 0.5 => 'mid',
                default => 'low',
            };
            $levelLabel = match ($level) {
                'good' => 'Excellent match',
                'mid' => 'Good match',
                default => 'Moderate match',
            };

            $rankBg = match ($rank) {
                1 => '#f59e0b',
                2 => '#94a3b8',
                3 => '#f97316',
                default => 'var(--rvc-rank-bg)',
            };
            $rankText = in_array($rank, [1, 2, 3], true) ? '#ffffff' : 'var(--rvc-rank-text)';

            $statusValue = $rec->status?->value ?? 'pending';
            $statusLabel = $statusValue === 'pending' ? 'Pending' : Str::title(str_replace('_', ' ', $statusValue));
            $pillBg = match ($statusValue) {
                'approved' => 'var(--rvc-pill-approved-bg)',
                'rejected' => 'var(--rvc-pill-rejected-bg)',
                default => 'var(--rvc-pill-pending-bg)',
            };
            $pillText = match ($statusValue) {
                'approved' => 'var(--rvc-pill-approved-text)',
                'rejected' => 'var(--rvc-pill-rejected-text)',
                default => 'var(--rvc-pill-pending-text)',
            };

            $sizeLabel = match (true) {
                isset($ex['team_size']) => $ex['team_size'].' active member'.($ex['team_size'] != 1 ? 's' : ''),
                isset($ex['department_size']) => $ex['department_size'].' active employee'.($ex['department_size'] != 1 ? 's' : ''),
                default => null,
            };

            $rows = [
                ['Skills', $skills['matched'].'/'.$skills['total'], $skillPct, '#10b981'],
                ['Certifications', $certs['matched'].'/'.$certs['total'], $certPct, '#0ea5e9'],
                ['Languages', $langs['matched'].'/'.$langs['total'], $langPct, '#a855f7'],
                ['Availability', $availPct.'%', $availPct, '#6366f1'],
                ['Workload used', $usedPct.'%', $usedPct, '#fb7185'],
            ];
        @endphp

        <div style="background: var(--rvc-soft-{{ $level }}); border: 1px solid var(--rvc-border); border-radius: 12px; padding: 16px 18px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="flex: none; min-width: 32px; height: 26px; padding: 0 9px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; letter-spacing: 0.02em; background: {{ $rankBg }}; color: {{ $rankText }};">
                    #{{ $rank }}
                </div>

                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 14px; font-weight: 650; color: var(--rvc-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $name }}</div>
                    <div style="font-size: 12px; color: var(--rvc-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $subtitle }}@if ($sizeLabel)<span style="color: var(--rvc-dim);"> · {{ $sizeLabel }}</span>@endif
                    </div>
                </div>

                <div style="flex: none; display: flex; flex-direction: column; align-items: flex-end; gap: 3px;">
                    <div style="font-size: 22px; font-weight: 700; line-height: 1; color: var(--rvc-{{ $level }});">{{ $pct }}%</div>
                    <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--rvc-dim);">match</div>
                    <span style="margin-top: 4px; padding: 3px 10px; border-radius: 9999px; font-size: 10px; font-weight: 600; line-height: 1; color: {{ $pillText }}; background: {{ $pillBg }};">{{ $statusLabel }}</span>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--rvc-border);">
                @foreach ($rows as [$label, $value, $p, $color])
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="flex: none; width: 110px; font-size: 11px; font-weight: 500; color: var(--rvc-muted);">{{ $label }}</span>
                        <div style="flex: 1; height: 6px; border-radius: 9999px; background: var(--rvc-track); overflow: hidden;">
                            <div style="height: 100%; width: {{ $p }}%; border-radius: 9999px; background: {{ $color }};"></div>
                        </div>
                        <span style="flex: none; width: 40px; text-align: right; font-size: 11px; font-weight: 650; color: var(--rvc-text);">{{ $value }}</span>
                    </div>
                @endforeach
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--rvc-border);">
                <span style="font-size: 11px; font-weight: 600; color: var(--rvc-{{ $level }});">{{ $levelLabel }}</span>
                <span style="font-size: 11px; color: var(--rvc-dim);">Overall recommendation score</span>
            </div>
        </div>
    @empty
        <div style="border: 1px dashed var(--rvc-border); border-radius: 12px; background: var(--rvc-soft); padding: 34px 20px; text-align: center;">
            <div style="font-size: 14px; font-weight: 600; color: var(--rvc-text);">No recommendations available</div>
            @if (! empty($blockers))
                <div style="margin-top: 12px; display: inline-block; text-align: left; font-size: 12px; color: var(--rvc-muted);">
                    <div style="font-weight: 600; margin-bottom: 6px;">No candidate met the mandatory requirements:</div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        @foreach ($blockers as $blocker)
                            <div style="color: var(--rvc-low);">• {{ $blocker }}</div>
                        @endforeach
                    </div>
                </div>
            @else
                <div style="margin-top: 6px; font-size: 12px; color: var(--rvc-dim);">Run the recommendation engine to generate candidate matches.</div>
            @endif
        </div>
    @endforelse
</div>