<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EvaluationsController extends Controller
{
    public function index(Request $request): Response
    {
        $employee = $request->user()->employee;

        $evaluations = $employee->evaluations()
            ->with(['project'])
            ->orderByDesc('evaluated_at')
            ->get()
            ->map(function ($evaluation) {
                return [
                    ...$evaluation->only([
                        'id',
                        'rating',
                        'communication_rating',
                        'delivery_rating',
                        'quality_rating',
                        'comments',
                        'evaluated_at',
                    ]),
                    'project' => $evaluation->project?->only(['id', 'name']),
                ];
            })
            ->values();

        return Inertia::render('portal/evaluations', [
            'evaluations' => $evaluations,
        ]);
    }
}
