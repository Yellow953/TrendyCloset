<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewOrder extends Mailable
{
    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New order '.$this->order->order_number.' · '.Product::money($this->order->grand_total).' · '.$this->order->ship_name,
            replyTo: $this->order->email ? [$this->order->email] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.new-order');
    }
}
