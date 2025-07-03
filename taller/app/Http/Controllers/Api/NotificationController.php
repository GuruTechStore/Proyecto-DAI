<?php
// app/Http/Controllers/Api/NotificationController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware(['auth', 'active']);
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $notifications = $this->notificationService->getNotificationsForUser(
            auth()->id(),
            $request->boolean('unread_only'),
            $request->get('limit', 20)
        );

        return response()->json($notifications);
    }

    public function unreadCount()
    {
        $count = $this->notificationService->getUnreadCount(auth()->id());
        return response()->json(['count' => $count]);
    }

    public function markAsRead(Notificacion $notification)
    {
        $success = $this->notificationService->markAsRead($notification->id, auth()->id());
        return response()->json(['success' => $success]);
    }

    public function markAllAsRead()
    {
        $count = $this->notificationService->markAllAsRead(auth()->id());
        return response()->json(['count' => $count]);
    }
}