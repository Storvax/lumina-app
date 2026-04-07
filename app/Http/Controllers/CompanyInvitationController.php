<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CompanyInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanyInvitationController extends Controller
{
    /**
     * Lista de colaboradores e convites pendentes da empresa do utilizador.
     */
    public function index(): View
    {
        $company = Auth::user()->company;

        if (! $company || Auth::user()->company_role !== 'hr_admin') {
            abort(403);
        }

        $employees    = $company->users()->orderBy('name')->get();
        $invitations  = $company->invitations()->orderByDesc('created_at')->get();

        return view('corporate.employees', compact('company', 'employees', 'invitations'));
    }

    /**
     * Envia um convite por email para um novo colaborador.
     * Rejeita duplicados (email já na empresa ou convite pendente).
     */
    public function invite(Request $request): RedirectResponse
    {
        $company = Auth::user()->company;

        if (! $company || Auth::user()->company_role !== 'hr_admin') {
            abort(403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:254'],
            'role'  => ['required', 'in:employee,hr_admin'],
        ]);

        // Verifica se o email já é colaborador desta empresa
        $alreadyMember = $company->users()->where('email', $validated['email'])->exists();

        if ($alreadyMember) {
            return back()->withErrors(['email' => 'Este endereço já pertence a um colaborador da empresa.']);
        }

        // Atualiza convite existente se caducado; rejeita se pendente
        $existing = CompanyInvitation::where('company_id', $company->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existing && $existing->isPending()) {
            return back()->withErrors(['email' => 'Já existe um convite pendente para este endereço.']);
        }

        if ($existing) {
            // Reutiliza o registo para evitar duplicação — atualiza token e prazo
            $existing->update([
                'token'       => Str::random(64),
                'role'        => $validated['role'],
                'expires_at'  => now()->addDays(7),
                'accepted_at' => null,
            ]);
            $invitation = $existing->fresh();
        } else {
            $invitation = CompanyInvitation::create([
                'company_id' => $company->id,
                'email'      => $validated['email'],
                'token'      => Str::random(64),
                'role'       => $validated['role'],
                'expires_at' => now()->addDays(7),
            ]);
        }

        // Envia email de convite (queued para não bloquear o request)
        Mail::to($invitation->email)->queue(
            new \App\Mail\CompanyInvitationMail($invitation, $company)
        );

        return back()->with('success', "Convite enviado para {$invitation->email}.");
    }

    /**
     * Aceita o convite e associa o utilizador autenticado à empresa.
     * Se o utilizador não está autenticado, redireciona para o registo.
     */
    public function accept(string $token): RedirectResponse
    {
        $invitation = CompanyInvitation::where('token', $token)->firstOrFail();

        if (! $invitation->isPending()) {
            return redirect()->route('home')
                ->withErrors(['invite' => 'Este convite já não é válido ou expirou.']);
        }

        if (! Auth::check()) {
            // Guarda o token em sessão para retomar após autenticação
            session(['pending_invite_token' => $token]);
            return redirect()->route('register')
                ->with('info', 'Cria a tua conta para aceitar o convite da empresa.');
        }

        $user = Auth::user();

        // O email do utilizador deve corresponder ao do convite
        if ($user->email !== $invitation->email) {
            return redirect()->route('dashboard')
                ->withErrors(['invite' => 'Este convite não é para a tua conta.']);
        }

        $user->update([
            'company_id'   => $invitation->company_id,
            'company_role' => $invitation->role,
        ]);

        $invitation->update(['accepted_at' => now()]);

        return redirect()->route('dashboard')
            ->with('success', "Bem-vindo(a) à {$invitation->company->name}!");
    }

    /**
     * Remove um colaborador da empresa (sem eliminar a conta do utilizador).
     */
    public function removeEmployee(Request $request): RedirectResponse
    {
        $company = Auth::user()->company;

        if (! $company || Auth::user()->company_role !== 'hr_admin') {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        // Impede que o admin se remova a si próprio
        if ((int) $validated['user_id'] === Auth::id()) {
            return back()->withErrors(['user_id' => 'Não podes remover-te a ti próprio.']);
        }

        $company->users()
            ->where('id', $validated['user_id'])
            ->update(['company_id' => null, 'company_role' => 'employee']);

        return back()->with('success', 'Colaborador removido da empresa.');
    }
}
