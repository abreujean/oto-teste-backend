<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $status;
    protected $message;

    public function __construct(Order $order, string $status, string $message)
    {
        $this->order = $order;
        $this->status = $status;
        $this->message = $message;
    }


    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
        ->subject('Atualização do seu Pedido #' . $this->order->id)
        ->greeting('Olá, ' . $notifiable->name . '!')
        ->line('O status do seu pedido foi atualizado para: ' . $this->status)
        ->line(( $this->message != null ? $this->message : '') . ', acesse o sistema e veja o pedido ' . $this->order->id)
        ->line('Obrigado por comprar conosco!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
        ];
    }
}
