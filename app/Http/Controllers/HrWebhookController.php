<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CompanyInvitation;
use App\Models\HrWebhookConfiguration;
use App\Models\HrWebhookLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HrWebhookController extends Controller
{
    /**
     * Endpoint público para receção de webhooks de sistemas HR externos (SAP, Workday).
     * Verifica assinatura HMAC antes de processar qualquer evento.
     */
    public function receive(Request $request, string $companySlug): JsonResponse
    {
        $config = HrWebhookConfiguration::whereHas('company', fn ($q) => $q->where('slug', $companySlug))
            ->where('is_active', true)
            ->first();

        if (! $config) {
            return response()->json(['error' => 'Configuração não encontrada.'], 404);
        }

        // Verificação de assinatura HMAC-SHA256 — protege contra payloads forjados
        if (! $this->verifySignature($request, $config->secret_token)) {
            return response()->json(['error' => 'Assinatura inválida.'], 401);
        }

        $payload   = $request->json()->all();
        $eventType = $request->header('X-HR-Event') ?? ($payload['event'] ?? 'unknown');

        $log = HrWebhookLog::create([
            'company_id' => $config->company_id,
            'provider'   => $config->provider,
            'event_type' => $eventType,
            'payload'    => $payload,
            'status'     => 'received',
        ]);

        // Só processa eventos subscritos pela configuração da empresa
        if (! \in_array($eventType, $config->event_types, true)) {
            $log->update(['status' => 'ignored']);
            return response()->json(['status' => 'ignored']);
        }

        try {
            $this->processEvent($eventType, $payload, $config->company_id);
            $log->update(['status' => 'processed', 'processed_at' => now()]);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            return response()->json(['error' => 'Erro interno ao processar evento.'], 500);
        }

        return response()->json(['status' => 'processed']);
    }

    /**
     * Processa os tipos de evento suportados.
     *
     * @param array<string, mixed> $payload
     */
    private function processEvent(string $eventType, array $payload, int $companyId): void
    {
        match ($eventType) {
            'employee.created'    => $this->handleEmployeeCreated($payload, $companyId),
            'employee.terminated' => $this->handleEmployeeTerminated($payload, $companyId),
            default               => null, // Eventos futuros ignorados graciosamente
        };
    }

    /**
     * Cria um convite automático para um novo colaborador adicionado no sistema HR.
     *
     * @param array<string, mixed> $payload
     */
    private function handleEmployeeCreated(array $payload, int $companyId): void
    {
        $email = $payload['email'] ?? null;

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        // Evita duplicação se o utilizador já existe na empresa
        $alreadyMember = User::where('email', $email)
            ->where('company_id', $companyId)
            ->exists();

        if ($alreadyMember) {
            return;
        }

        // Cria ou renova convite (upsert por company_id + email)
        CompanyInvitation::updateOrCreate(
            ['company_id' => $companyId, 'email' => $email],
            [
                'token'       => Str::random(64),
                'role'        => 'employee',
                'expires_at'  => now()->addDays(14),
                'accepted_at' => null,
            ]
        );
    }

    /**
     * Dissocia um colaborador da empresa ao ser desligado no sistema HR.
     * Preserva a conta do utilizador — não elimina dados.
     *
     * @param array<string, mixed> $payload
     */
    private function handleEmployeeTerminated(array $payload, int $companyId): void
    {
        $email = $payload['email'] ?? null;

        if (! $email) {
            return;
        }

        User::where('email', $email)
            ->where('company_id', $companyId)
            ->update(['company_id' => null, 'company_role' => 'employee']);

        // Cancela convites pendentes para este email
        CompanyInvitation::where('company_id', $companyId)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->delete();
    }

    /**
     * Verifica assinatura HMAC-SHA256 do payload.
     * Suporta header X-Hub-Signature-256 (Workday/genérico) e X-SAP-Signature.
     */
    private function verifySignature(Request $request, string $secret): bool
    {
        $rawBody   = $request->getContent();
        $expected  = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);

        $signature = $request->header('X-Hub-Signature-256')
            ?? $request->header('X-SAP-Signature')
            ?? '';

        return Hash::check($expected, Hash::make($signature))
            // hash_equals previne timing attacks
            || hash_equals($expected, $signature);
    }
}
