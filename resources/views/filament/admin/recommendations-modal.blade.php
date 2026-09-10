<style>
    .recommendation-list {
        --rv-bg: #ffffff;
        --rv-soft: #f8fafc;
        --rv-border: #e2e8f0;
        --rv-track: #e2e8f0;
        --rv-text: #0f172a;
        --rv-muted: #475569;
        --rv-dim: #94a3b8;
        --rv-success: #059669;
        --rv-amber: #b45309;
        --rv-badge-default: #e2e8f0;
        --rv-badge-default-text: #475569;
        --rv-soft-good: #ecfdf5;
        --rv-soft-mid: #fffbeb;
        --rv-soft-low: #fff1f2;
        --rv-good: #059669;
        --rv-mid: #d97706;
        --rv-low: #e11d48;
        --rv-pill-pending-bg: #f1f5f9;
        --rv-pill-pending-text: #475569;
        --rv-pill-approved-bg: #d1fae5;
        --rv-pill-approved-text: #059669;
        --rv-pill-rejected-bg: #ffe4e6;
        --rv-pill-rejected-text: #e11d48;
    }

    .dark .recommendation-list {
        --rv-bg: #1e293b;
        --rv-soft: #0f172a;
        --rv-border: #334155;
        --rv-track: #3f4d61;
        --rv-text: #e2e8f0;
        --rv-muted: #94a3b8;
        --rv-dim: #64748b;
        --rv-amber: #fbbf24;
        --rv-success: #34d399;
        --rv-badge-default: #334155;
        --rv-badge-default-text: #cbd5e1;
        --rv-soft-good: #1e293b;
        --rv-soft-mid: #1e293b;
        --rv-soft-low: #1e293b;
        --rv-good: #34d399;
        --rv-mid: #fbbf24;
        --rv-low: #fb7185;
        --rv-pill-pending-bg: #334155;
        --rv-pill-pending-text: #cbd5e1;
        --rv-pill-approved-bg: #064e3b;
        --rv-pill-approved-text: #6ee7b7;
        --rv-pill-rejected-bg: #4c0519;
        --rv-pill-rejected-text: #fda4af;
    }
</style>

<div class="recommendation-list" style="display: flex; flex-direction: column; gap: 14px;">
    @forelse ($recommendations as $rec)
        @php
            $rank = (int) $rec->rank;
            $name = $rec->employee?->full_name ?? ($rec->team?->name ?? ($rec->department?->name ?? 'N/A'));
            $subtitle = $rec->employee_id
                ? ($rec->employee?->employee_code ?? '')
                : ($rec->team_id
                    ? 'Team · '.($rec->team?->code ?? '')
                    : 'Department · '.($rec->department?->code ?? ''));
            $initials = Str::of(trim($name))
                ->split('/[\s\-]+/')
                ->reject(fn ($w) => trim((string) $w) === '')
                ->take(2)
                ->map(fn ($w) => Str::upper(Str::substr((string) $w, 0, 1)))
                ->implode('');

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

            $rankBadge = match ($rank) {
                1 => ['#f59e0b', 'Best match'],
                2 => ['#94a3b8', null],
                3 => ['#f97316', null],
                default => [null, null],
            };
            $rankBadgeBg = $rankBadge[0] ?? 'var(--rv-badge-default)';
            $rankBadgeText = $rankBadge[0] ? '#ffffff' : 'var(--rv-badge-default-text)';

            $statusValue = $rec->status?->value ?? 'pending';
            $statusLabel = Str::title(str_replace('_', ' ', $statusValue));
            $pillBg = match ($statusValue) {
                'approved' => 'var(--rv-pill-approved-bg)',
                'rejected' => 'var(--rv-pill-rejected-bg)',
                default => 'var(--rv-pill-pending-bg)',
            };
            $pillText = match ($statusValue) {
                'approved' => 'var(--rv-pill-approved-text)',
                'rejected' => 'var(--rv-pill-rejected-text)',
                default => 'var(--rv-pill-pending-text)',
            };

            $circumference = 2 * M_PI * 20;
            $dashOffset = $circumference * (1 - max(0, min(1, $score)));

            $sizeLabel = match (true) {
                isset($ex['team_size']) => $ex['team_size'].' active member'.($ex['team_size'] != 1 ? 's' : ''),
                isset($ex['department_size']) => $ex['department_size'].' active employee'.($ex['department_size'] != 1 ? 's' : ''),
                default => null,
            };

            $factors = [
                ['Skills', $skills['matched'], $skills['total'], $skillPct, '#10b981'],
                ['Certifications', $certs['matched'], $certs['total'], $certPct, '#0ea5e9'],
                ['Languages', $langs['matched'], $langs['total'], $langPct, '#8b5cf6'],
                ['Availability', null, null, $availPct, '#6366f1'],
                ['Workload used', null, null, $usedPct, '#fb7185'],
            ];
        @endphp

        <div style="border: 1px solid var(--rv-border); border-radius: 14px; background: var(--rv-soft-{{ $level }}); overflow: hidden;">
            <div style="height: 3px; background: var(--rv-{{ $level }});"></div>
            <div style="display: flex; align-items: center; gap: 14px; padding: 16px 18px;">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 2px; width: 34px;">
                    <div style="width: 30px; height: 30px; border-radius: 9px; background: {{ $rankBadgeBg }}; color: {{ $rankBadgeText }}; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;">
                        #{{ $rank }}
                    </div>
                    @if ($rankBadge[1])
                        <div style="font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--rv-amber);">{{ $rankBadge[1] }}</div>
                    @endif
                </div>

                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0;">
                    {{ $initials ?: '?' }}
                </div>

                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <div style="font-weight: 700; font-size: 14px; color: var(--rv-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $name }}</div>
                        @if ($statusValue === 'approved')
                            <svg style="width: 15px; height: 15px; color: var(--rv-success); flex-shrink: 0;" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.172 7.707 8.879a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </div>
                    <div style="font-size: 12px; color: var(--rv-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $subtitle }}</div>
                    @if ($sizeLabel)
                        <div style="font-size: 10px; color: var(--rv-dim); margin-top: 2px;">{{ $sizeLabel }}</div>
                    @endif
                </div>

                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0;">
                    <div style="position: relative; width: 66px; height: 66px;">
                        <svg viewBox="0 0 48 48" style="width: 66px; height: 66px; transform: rotate(-90deg);">
                            <circle cx="24" cy="24" r="20" fill="none" stroke-width="4" style="stroke: var(--rv-track);"/>
                            <circle cx="24" cy="24" r="20" fill="none" stroke-width="4" stroke-linecap="round" style="stroke: var(--rv-{{ $level }}); stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $dashOffset }};"/>
                        </svg>
                        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <span style="font-size: 15px; font-weight: 700; color: var(--rv-{{ $level }}); line-height: 1;">{{ $pct }}%</span>
                            <span style="font-size: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--rv-dim); margin-top: 2px;">match</span>
                        </div>
                    </div>
                    <span style="padding: 2px 10px; border-radius: 9999px; font-size: 10px; font-weight: 600; color: {{ $pillText }}; background: {{ $pillBg }};">{{ $statusLabel }}</span>
                </div>
            </div>

            <div style="padding: 12px 18px; background: var(--rv-soft); border-top: 1px solid var(--rv-border);">
                <div style="display: flex; flex-wrap: wrap; gap: 12px 22px;">
                    @foreach ($factors as [$label, $matched, $total, $p, $color])
                        <div style="flex: 1 1 170px; min-width: 150px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                <span style="font-size: 10px; font-weight: 500; color: var(--rv-muted);">{{ $label }}</span>
                                <span style="font-size: 10px; font-weight: 700; color: var(--rv-text);">{{ $total !== null ? "{$matched}/{$total}" : $p.'%' }}</span>
                            </div>
                            <div style="height: 6px; border-radius: 9999px; background: var(--rv-track); overflow: hidden;">
                                <div style="height: 100%; width: {{ $p }}%; background: {{ $color }}; border-radius: 9999px;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="display: flex; align-items: center; gap: 10px; margin-top: 12px; padding-top: 10px; border-top: 1px solid var(--rv-border);">
                    <span style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--rv-{{ $level }});">
                        {{ $levelLabel }}
                    </span>
                    <span style="font-size: 10px; color: var(--rv-dim); margin-left: auto;">Overall recommendation score</span>
                </div>
            </div>
        </div>
    @empty
        <div style="border: 1px dashed var(--rv-border); border-radius: 14px; background: var(--rv-soft); padding: 36px 20px; text-align: center;">
            <div style="font-size: 13px; font-weight: 600; color: var(--rv-text);">No recommendations available</div>
            <div style="font-size: 11px; color: var(--rv-dim); margin-top: 4px;">Run the recommendation engine to generate candidate matches.</div>
        </div>
    @endforelse
</div>