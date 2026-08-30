<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
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
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->kind,
            'title_key' => $this->titleKey,
            'message_key' => $this->messageKey,
            'parameters' => $this->parameters,
            'route_name' => $this->routeName,
            'route_parameters' => $this->routeParameters,
            'icon' => $this->icon,
        ];
    }
}
