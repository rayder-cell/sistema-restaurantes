<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PedidoListoNotification extends Notification
{
    public function __construct(
        public int $pedidoId,
        public ?string $mesaNumero
    ) {}

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('🍽️ Pedido listo')
            ->body("Mesa {$this->mesaNumero} — el pedido #{$this->pedidoId} está listo para entregar")
            ->data(['url' => '/mesas'])
            ->options(['TTL' => 300]);
    }
}