<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationsController extends Controller
{
    public function index(Request $request): Response
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'data' => $notification->data,
                'read' => $notification->read_at !== null,
                'created_at' => $notification->created_at?->diffForHumans(),
            ])
            ->values();

        return Inertia::render('portal/notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function recent(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'data' => $notification->data,
                'read' => $notification->read_at !== null,
                'created_at' => $notification->created_at?->diffForHumans(),
            ]);

        return response()->json(['notifications' => $notifications]);
    }
}
