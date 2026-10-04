<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderReceived extends Mailable
{
    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We have your order '.$this->order->order_number,
            replyTo: array_filter([config('seo.email')]),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.order-received');
    }
}
