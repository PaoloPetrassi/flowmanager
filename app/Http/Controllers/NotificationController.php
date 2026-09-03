<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'all');
        $status = in_array($status, ['all', 'unread', 'read'], true) ? $status : 'all';

        $category = (string) $request->query('category', '');
        $categories = $this->categories();
        $category = array_key_exists($category, $categories) ? $category : '';

        $notifications = $request->user()
            ->notifications()
            ->when($status === 'unread', fn (Builder $query) => $query->whereNull('read_at'))
            ->when($status === 'read', fn (Builder $query) => $query->whereNotNull('read_at'))
            ->when($category !== '', fn (Builder $query) => $query->where('data->category', $category))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'categories' => $categories,
            'filters' => [
                'status' => $status,
                'category' => $category,
            ],
            'unreadCount' => $request->user()->unreadNotifications()->count(),
            'readCount' => $request->user()->readNotifications()->count(),
        ]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $notificationModel = $this->notificationFor($request, $notification);
        $notificationModel->markAsRead();

        $routeName = $notificationModel->data['route_name'] ?? null;
        $parameters = $notificationModel->data['route_parameters'] ?? [];

        if ($routeName && Route::has($routeName)) {
            return redirect()->route($routeName, $parameters);
        }

        return redirect()->route('notifications.index');
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $this->notificationFor($request, $notification)->markAsRead();

        return back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', __('All notifications marked as read.'));
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $this->notificationFor($request, $notification)->delete();

        return back()->with('status', __('Notification deleted.'));
    }

    public function clearRead(Request $request): RedirectResponse
    {
        $count = $request->user()->readNotifications()->count();
        $request->user()->readNotifications()->delete();

        return back()->with('status', __('Deleted :count read notifications.', ['count' => $count]));
    }

    private function notificationFor(Request $request, string $id): DatabaseNotification
    {
        /** @var DatabaseNotification $notification */
        $notification = $request->user()->notifications()->findOrFail($id);

        return $notification;
    }

    /**
     * @return array<string, string>
     */
    private function categories(): array
    {
        return [
            'assignments' => __('Assignments'),
            'comments' => __('Comments'),
            'reminders' => __('Reminders'),
            'automations' => __('Automations'),
            'system' => __('System'),
        ];
    }
}
