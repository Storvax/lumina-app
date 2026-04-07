<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Company;
use App\Models\CompanyInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly CompanyInvitation $invitation,
        public readonly Company $company,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Convite para te juntares à {$this->company->name} no Lumina",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.company-invitation',
            with: [
                'acceptUrl' => route('company.invite.accept', $this->invitation->token),
                'company'   => $this->company,
                'expiresAt' => $this->invitation->expires_at->translatedFormat('d \d\e F \d\e Y'),
            ],
        );
    }
}
