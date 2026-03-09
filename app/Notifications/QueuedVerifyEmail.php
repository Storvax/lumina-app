<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Versão assíncrona da notificação de verificação de email.
 *
 * O VerifyEmail nativo do Laravel é síncrono — em produção com SMTP externo
 * (Brevo, SES, etc.) o handshake TLS + envio pode exceder o timeout do proxy
 * (Railway = 60s), resultando em 504 Gateway Timeout.
 *
 * Ao implementar ShouldQueue, o envio é despachado para a queue (database)
 * e processado pelo worker em background, libertando o request imediatamente.
 */
class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        // Fila dedicada para emails críticos de autenticação
        $this->onQueue('auth-mail');
    }
}
