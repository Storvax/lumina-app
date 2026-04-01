<?php

/**
 * Configuração explícita de CORS (Cross-Origin Resource Sharing).
 *
 * `allowed_origins` restrito ao domínio da app em vez de wildcard '*'.
 * O wildcard exporia a API a qualquer origem, facilitando ataques CSRF em browsers.
 * Em desenvolvimento local, APP_URL inclui o host do servidor (ex: http://localhost:8000).
 *
 * Notas de segurança:
 *  - A API móvel (sanctum) usa tokens Bearer — não depende de cookies, logo CORS
 *    é o principal controlo de origem para clientes web externos.
 *  - `supports_credentials` permanece false: tokens Sanctum são enviados no header,
 *    não em cookies, pelo que credenciais cross-origin não são necessárias.
 */
return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // Em produção, apenas o domínio da app. APP_CORS_ORIGINS permite override via .env
    // para ambientes staging ou apps móveis com domínio próprio.
    'allowed_origins' => array_filter(
        array_map('trim', explode(',', env('APP_CORS_ORIGINS', env('APP_URL', 'http://localhost'))))
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'Accept', 'X-XSRF-TOKEN'],

    'exposed_headers' => [],

    'max_age' => 86400, // Cache preflight 24h para reduzir latência

    'supports_credentials' => false,

];
