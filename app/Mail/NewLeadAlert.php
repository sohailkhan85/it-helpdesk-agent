<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewLeadAlert extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $email;
    public string $issue;

    public function __construct(string $name, string $email, ?string $issue = null)
    {
        $this->name  = $name;
        $this->email = $email;
        $this->issue = $issue ?? 'No issue provided';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔔 New IT Helpdesk Lead — ' . $this->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-lead',
        );
    }
}