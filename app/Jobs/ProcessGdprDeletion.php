<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\DataPurgedCertificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

/**
 * Executa o apagamento em cascata e assíncrono de todos os dados do utilizador.
 * Previne sobrecarga da base de dados (timeouts) e garante conformidade com o RGPD.
 */
class ProcessGdprDeletion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected string $userEmail;

    public function __construct(User $user)
    {
        $this->userId = $user->id;
        $this->userEmail = $user->email;
    }

    public function handle(): void
    {
        // Bloqueia a transação para garantir integridade atómica
        DB::transaction(function () {
            $user = User::withTrashed()->find($this->userId);
            
            if (!$user) return;

            // Purga explícita (mesmo que haja onDeleteCascade, previne dados órfãos em polymorphic relations)
            $user->dailyLogs()->delete();
            $user->posts()->delete();
            $user->comments()->delete();
            $user->buddySessions()->delete();
            
            // Remove as associações pivot
            $user->savedPosts()->detach();
            $user->subscribedPosts()->detach();
            $user->achievements()->detach();

            // Apagamento físico definitivo
            $user->forceDelete();
        });

        Log::info("Processo RGPD: Dados do utilizador ID {$this->userId} foram totalmente purgados.");

        // Despacha o certificado de eliminação
        Mail::to($this->userEmail)->send(new DataPurgedCertificate());
    }
}