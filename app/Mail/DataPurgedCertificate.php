<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notificação legal enviada após a conclusão do processo de Direito ao Esquecimento.
 */
class DataPurgedCertificate extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lumina: Confirmação de Eliminação de Dados (RGPD)',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.gdpr.purged', // Precisamos criar esta view
        );
    }
}