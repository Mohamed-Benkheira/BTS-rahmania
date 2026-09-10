<div class="space-y-3">
    @forelse ($recommendations as $rec)
        <div class="rounded-lg border p-4 {{ match($rec->status) {
            'approved' => 'border-green-300 bg-green-50',
            'rejected' => 'border-red-300 bg-red-50',
            default => 'border-gray-200 bg-white',
        } }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center rounded-full bg-primary-100 px-3 py-1 text-sm font-semibold text-primary-700">
                        #{{ $rec->rank }}
                    </span>
                    <div>
                        <p class="font-semibold text-gray-900">
                            @if ($rec->employee_id)
                                {{ $rec->employee?->full_name ?? 'N/A' }}
                            @elseif ($rec->team_id)
                                {{ $rec->team?->name ?? 'N/A' }}
                            @elseif ($rec->department_id)
                                {{ $rec->department?->name ?? 'N/A' }}
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">
                            @if ($rec->employee_id)
                                {{ $rec->employee?->employee_code ?? '' }}
                            @elseif ($rec->team_id)
                                Team &middot; {{ $rec->team?->code ?? '' }}
                            @elseif ($rec->department_id)
                                Department &middot; {{ $rec->department?->code ?? '' }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold {{ match(true) {
                        $rec->total_score >= 0.8 => 'text-green-600',
                        $rec->total_score >= 0.5 => 'text-yellow-600',
                        default => 'text-red-600',
                    } }}">
                        {{ number_format($rec->total_score * 100, 1) }}%
                    </p>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ match($rec->status) {
                        'approved' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800',
                    } }}">
                        {{ ucfirst($rec->status->value) }}
                    </span>
                </div>
            </div>

            @if ($rec->explanation)
                <div class="mt-3 grid grid-cols-5 gap-2 text-center text-xs">
                    <div>
                        <p class="text-gray-500">Skills</p>
                        <p class="font-semibold">{{ $rec->explanation['skills']['matched'] ?? 0 }}/{{ $rec->explanation['skills']['total'] ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Certs</p>
                        <p class="font-semibold">{{ $rec->explanation['certifications']['matched'] ?? 0 }}/{{ $rec->explanation['certifications']['total'] ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Languages</p>
                        <p class="font-semibold">{{ $rec->explanation['languages']['matched'] ?? 0 }}/{{ $rec->explanation['languages']['total'] ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Availability</p>
                        <p class="font-semibold">{{ number_format(($rec->explanation['availability'] ?? 0) * 100, 0) }}%</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Workload</p>
                        <p class="font-semibold">{{ number_format((1 - ($rec->explanation['workload'] ?? 0)) * 100, 0) }}% used</p>
                    </div>
                </div>
                @if (isset($rec->explanation['team_size']) || isset($rec->explanation['department_size']))
                    <p class="mt-2 text-center text-xs text-gray-400">
                        @if (isset($rec->explanation['team_size']))
                            {{ $rec->explanation['team_size'] }} active member(s)
                        @endif
                        @if (isset($rec->explanation['department_size']))
                            {{ $rec->explanation['department_size'] }} active employee(s)
                        @endif
                    </p>
                @endif
            @endif
        </div>
    @empty
        <p class="text-center text-gray-500">No recommendations in this run.</p>
    @endforelse
</div>
