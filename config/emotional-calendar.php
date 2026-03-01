<?php

/**
 * Calendário Emocional Português.
 *
 * Mapeia datas com impacto emocional cultural a mensagens contextuais
 * apresentadas no dashboard. As datas seguem o formato MM-DD.
 * Cada entrada define um tipo emocional (grief, celebration, awareness, family)
 * que orienta o tom visual do card.
 */
return [
    '01-01' => [
        'title'   => 'Ano Novo',
        'message' => 'Um novo começo. Não precisas de grandes resoluções — só de gentileza contigo.',
        'type'    => 'celebration',
        'icon'    => 'ri-sparkling-line',
    ],
    '02-14' => [
        'title'   => 'Dia dos Namorados',
        'message' => 'Hoje celebra-se o amor — e isso inclui o amor que tens por ti. Sê gentil contigo.',
        'type'    => 'awareness',
        'icon'    => 'ri-heart-line',
    ],
    '03-20' => [
        'title'   => 'Dia Internacional da Felicidade',
        'message' => 'Felicidade não é constante — é um momento. Está tudo bem se hoje não for esse dia.',
        'type'    => 'awareness',
        'icon'    => 'ri-emotion-happy-line',
    ],
    '04-07' => [
        'title'   => 'Dia Mundial da Saúde',
        'message' => 'Saúde mental é saúde. Cuidar da mente é tão importante como cuidar do corpo.',
        'type'    => 'awareness',
        'icon'    => 'ri-mental-health-line',
    ],
    '04-25' => [
        'title'   => '25 de Abril',
        'message' => 'Liberdade também é isto: poder falar sobre o que sentes sem medo.',
        'type'    => 'celebration',
        'icon'    => 'ri-seedling-line',
    ],
    '05-04' => [
        'title'   => 'Dia da Mãe',
        'message' => 'Relações familiares podem ser complexas. O que quer que sintas hoje é válido.',
        'type'    => 'family',
        'icon'    => 'ri-parent-line',
    ],
    '06-10' => [
        'title'   => 'Dia de Portugal',
        'message' => 'Celebrar um país é também celebrar a sua gente — incluindo tu.',
        'type'    => 'celebration',
        'icon'    => 'ri-flag-line',
    ],
    '09-10' => [
        'title'   => 'Dia Mundial de Prevenção do Suicídio',
        'message' => 'Pedir ajuda é um acto de coragem, não de fraqueza. Estamos aqui. SNS 24: 808 24 24 24.',
        'type'    => 'awareness',
        'icon'    => 'ri-hand-heart-line',
    ],
    '10-10' => [
        'title'   => 'Dia Mundial da Saúde Mental',
        'message' => 'Hoje o mundo fala de saúde mental. Tu já falas todos os dias. Isso é extraordinário.',
        'type'    => 'awareness',
        'icon'    => 'ri-mental-health-line',
    ],
    '11-01' => [
        'title'   => 'Dia de Todos os Santos',
        'message' => 'Se o luto te visita hoje, não precisas de o esconder. Sentir saudade é humano.',
        'type'    => 'grief',
        'icon'    => 'ri-candle-line',
    ],
    '11-02' => [
        'title'   => 'Dia de Finados',
        'message' => 'Lembrar quem já partiu pode doer. Está tudo bem em sentir o que precisares.',
        'type'    => 'grief',
        'icon'    => 'ri-candle-line',
    ],
    '12-24' => [
        'title'   => 'Consoada',
        'message' => 'O Natal pode ser difícil para quem sente solidão ou perda. Não estás sozinho(a).',
        'type'    => 'family',
        'icon'    => 'ri-home-heart-line',
    ],
    '12-25' => [
        'title'   => 'Natal',
        'message' => 'Nem todos sentem alegria hoje — e isso é tão válido como qualquer celebração.',
        'type'    => 'family',
        'icon'    => 'ri-home-heart-line',
    ],
    '12-31' => [
        'title'   => 'Fim de Ano',
        'message' => 'Balanços podem ser pesados. O que importa é que chegaste até aqui.',
        'type'    => 'awareness',
        'icon'    => 'ri-time-line',
    ],
];
