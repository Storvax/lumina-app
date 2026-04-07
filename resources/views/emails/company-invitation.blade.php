<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convite Lumina</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 40px 20px; color: #1e293b; }
        .card { background: #ffffff; border-radius: 16px; max-width: 480px; margin: 0 auto; padding: 40px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); }
        .logo { font-size: 22px; font-weight: 900; color: #0d9488; margin-bottom: 32px; }
        h1 { font-size: 22px; font-weight: 800; margin: 0 0 12px; }
        p { font-size: 15px; line-height: 1.6; color: #475569; margin: 0 0 16px; }
        .btn { display: inline-block; background: #0d9488; color: #ffffff; font-weight: 700; font-size: 15px; text-decoration: none; padding: 14px 28px; border-radius: 12px; margin: 8px 0 24px; }
        .note { font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">✦ Lumina</div>
        <h1>Foste convidado(a) para a {{ $company->name }}</h1>
        <p>
            A tua empresa registou-se no <strong>Lumina</strong> — um espaço seguro e calmo para o bem-estar emocional e mental no trabalho.
        </p>
        <p>Aceita o convite para acederes aos programas de bem-estar e às ferramentas de suporte emocional disponibilizadas pela {{ $company->name }}.</p>
        <a href="{{ $acceptUrl }}" class="btn">Aceitar Convite</a>
        <p class="note">
            Este convite é válido até <strong>{{ $expiresAt }}</strong>.<br>
            Se não reconheces este convite, podes ignorar este email com segurança.
        </p>
    </div>
</body>
</html>
