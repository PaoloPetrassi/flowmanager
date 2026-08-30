<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function open(
        Request $request,
        string $notification
    ): RedirectResponse {
        /** @var DatabaseNotification $notificationModel */
        $notificationModel = $request->user()
            ->notifications()
            ->findOrFail($notification);

        $notificationModel->markAsRead();

        $routeName = $notificationModel->data['route_name'] ?? null;
        $parameters = $notificationModel->data['route_parameters'] ?? [];

        if ($routeName && Route::has($routeName)) {
            return redirect()->route(
                $routeName,
                $parameters
            );
        }

        return redirect()->route('notifications.index');
    }

    public function read(
        Request $request,
        string $notification
    ): RedirectResponse {
        $request->user()
            ->notifications()
            ->findOrFail($notification)
            ->markAsRead();

        return back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return back()->with(
            'status',
            __('All notifications marked as read.')
        );
    }
}
