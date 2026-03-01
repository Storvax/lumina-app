<?php

/**
 * Biblioteca Sonora de Portugal
 *
 * Categorias de sons ambientes inspirados em paisagens e cultura portuguesa.
 * Os ficheiros de áudio serão armazenados em storage/app/public/sounds/.
 */
return [
    'categories' => [
        'ocean' => [
            'name' => 'Costa Atlântica',
            'icon' => 'ri-water-flash-line',
            'color' => 'blue',
            'sounds' => [
                ['id' => 'nazare', 'name' => 'Ondas da Nazaré', 'file' => 'sounds/nazare-waves.mp3', 'duration' => '5:00'],
                ['id' => 'algarve', 'name' => 'Costa do Algarve', 'file' => 'sounds/algarve-coast.mp3', 'duration' => '5:00'],
                ['id' => 'cascais', 'name' => 'Boca do Inferno', 'file' => 'sounds/cascais-boca.mp3', 'duration' => '5:00'],
            ],
        ],
        'nature' => [
            'name' => 'Natureza Interior',
            'icon' => 'ri-plant-line',
            'color' => 'emerald',
            'sounds' => [
                ['id' => 'geres', 'name' => 'Rio no Gerês', 'file' => 'sounds/geres-river.mp3', 'duration' => '5:00'],
                ['id' => 'sintra', 'name' => 'Floresta de Sintra', 'file' => 'sounds/sintra-forest.mp3', 'duration' => '5:00'],
                ['id' => 'douro', 'name' => 'Vale do Douro', 'file' => 'sounds/douro-valley.mp3', 'duration' => '5:00'],
            ],
        ],
        'urban' => [
            'name' => 'Sons Urbanos',
            'icon' => 'ri-building-line',
            'color' => 'amber',
            'sounds' => [
                ['id' => 'tram28', 'name' => 'Elétrico 28', 'file' => 'sounds/tram28-lisbon.mp3', 'duration' => '5:00'],
                ['id' => 'fado', 'name' => 'Fado ao Longe', 'file' => 'sounds/fado-distant.mp3', 'duration' => '5:00'],
                ['id' => 'cafe', 'name' => 'Café Português', 'file' => 'sounds/portuguese-cafe.mp3', 'duration' => '5:00'],
            ],
        ],
        'rain' => [
            'name' => 'Chuva & Vento',
            'icon' => 'ri-rainy-line',
            'color' => 'slate',
            'sounds' => [
                ['id' => 'rain-porto', 'name' => 'Chuva no Porto', 'file' => 'sounds/rain-porto.mp3', 'duration' => '5:00'],
                ['id' => 'storm', 'name' => 'Tempestade Atlântica', 'file' => 'sounds/atlantic-storm.mp3', 'duration' => '5:00'],
                ['id' => 'garden-rain', 'name' => 'Chuva no Jardim', 'file' => 'sounds/garden-rain.mp3', 'duration' => '5:00'],
            ],
        ],
    ],
];
