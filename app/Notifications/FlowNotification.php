<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FlowNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $kind,
        private readonly string $titleKey,
        private readonly string $messageKey,
        private readonly array $parameters,
        private readonly string $routeName,
        private readonly array $routeParameters,
        private readonly string $icon = 'bi-bell'
    ) {
    }

    public function via(object $notifiable): array
    {
        $preferences = $notifiable->preference?->notification_preferences ?? [];
        $category = $this->category();
        $channels = [];

        if ((bool) data_get($preferences, 'in_app.'.$category, true)) {
            $channels[] = 'database';
        }

        if (
            config('flowmanager.notifications.mail_enabled')
            && (bool) data_get($preferences, 'mail.'.$category, true)
        ) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__($this->titleKey))
            ->line(__($this->messageKey, $this->parameters))
            ->action(__('Open in FlowManager'), route($this->routeName, $this->routeParameters));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->kind,
            'category' => $this->category(),
            'title_key' => $this->titleKey,
            'message_key' => $this->messageKey,
            'parameters' => $this->parameters,
            'route_name' => $this->routeName,
            'route_parameters' => $this->routeParameters,
            'icon' => $this->icon,
        ];
    }

    private function category(): string
    {
        return match ($this->kind) {
            'assignment', 'reassignment' => 'assignments',
            'comment' => 'comments',
            'task_reminder', 'sla_reminder' => 'reminders',
            'automation' => 'automations',
            default => 'system',
        };
    }
}
