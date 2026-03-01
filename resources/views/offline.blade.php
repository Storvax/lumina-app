<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sem ligação | Lumina</title>
    <meta name="theme-color" content="#6366f1">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
            text-align: center;
        }
        .container { max-width: 400px; }
        .icon {
            width: 80px; height: 80px;
            background: #eef2ff;
            border-radius: 1.5rem;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
        }
        h1 { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 0.5rem; }
        p { font-size: 0.9rem; line-height: 1.6; color: #64748b; margin-bottom: 1.5rem; }
        .breathe {
            width: 120px; height: 120px;
            border-radius: 50%;
            background: radial-gradient(circle, #c7d2fe 0%, #eef2ff 70%);
            margin: 2rem auto;
            animation: breathe 6s ease-in-out infinite;
        }
        @keyframes breathe {
            0%, 100% { transform: scale(0.8); opacity: 0.6; }
            50% { transform: scale(1.1); opacity: 1; }
        }
        .hint { font-size: 0.75rem; color: #94a3b8; font-style: italic; }
        button {
            margin-top: 1.5rem;
            padding: 0.75rem 2rem;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.875rem;
            cursor: pointer;
        }
        button:active { transform: scale(0.97); }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🌙</div>
        <h1>Sem ligação à internet</h1>
        <p>De momento não tens ligação, mas podes usar este momento para respirar.</p>
        <div class="breathe"></div>
        <p class="hint">Inspira enquanto o círculo cresce... Expira enquanto encolhe.</p>
        <button onclick="window.location.reload()">Tentar novamente</button>
    </div>
</body>
</html>
