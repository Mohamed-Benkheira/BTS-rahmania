<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RequestsController extends Controller
{
    public function index(Request $request): Response
    {
        $employee = $request->user()->employee;

        return Inertia::render('portal/my-requests', [
            'requests' => $employee->profileChangeRequests()
                ->with('reviewer')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }
}
