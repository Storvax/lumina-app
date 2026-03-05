<?php

/**
 * Biblioteca Sonora de Portugal
 *
 * Categorias de sons ambientes inspirados em paisagens e cultura portuguesa.
 * Ficheiros servidos via Google Actions Sound CDN para alta disponibilidade e velocidade.
 */
return [
    'categories' => [
        'ocean' => [
            'name' => 'Costa Atlântica',
            'icon' => 'ri-water-flash-line',
            'color' => 'blue',
            'sounds' => [
                ['id' => 'nazare', 'name' => 'Ondas da Nazaré', 'file' => 'https://actions.google.com/sounds/v1/water/waves_crash_heavy_swell.ogg', 'duration' => 'Loop'],
                ['id' => 'algarve', 'name' => 'Costa do Algarve', 'file' => 'https://actions.google.com/sounds/v1/water/ocean_waves_pebbles.ogg', 'duration' => 'Loop'],
                ['id' => 'cascais', 'name' => 'Boca do Inferno', 'file' => 'https://actions.google.com/sounds/v1/water/waves_crashing_on_rock_beach.ogg', 'duration' => 'Loop'],
            ],
        ],
        'nature' => [
            'name' => 'Natureza Interior',
            'icon' => 'ri-plant-line',
            'color' => 'emerald',
            'sounds' => [
                ['id' => 'geres', 'name' => 'Rio no Gerês', 'file' => 'https://actions.google.com/sounds/v1/water/small_stream_flowing.ogg', 'duration' => 'Loop'],
                ['id' => 'sintra', 'name' => 'Floresta de Sintra', 'file' => 'https://actions.google.com/sounds/v1/ambiences/summer_forest.ogg', 'duration' => 'Loop'],
                ['id' => 'douro', 'name' => 'Vale do Douro', 'file' => 'https://actions.google.com/sounds/v1/ambiences/meadow_morning.ogg', 'duration' => 'Loop'],
            ],
        ],
        'urban' => [
            'name' => 'Sons Urbanos',
            'icon' => 'ri-building-line',
            'color' => 'amber',
            'sounds' => [
                ['id' => 'tram28', 'name' => 'Elétrico 28', 'file' => 'https://actions.google.com/sounds/v1/transportation/passing_train.ogg', 'duration' => 'Loop'],
                ['id' => 'fado', 'name' => 'Acordes ao Longe', 'file' => 'https://actions.google.com/sounds/v1/human_voices/acoustic_guitar_strums.ogg', 'duration' => 'Loop'],
                ['id' => 'cafe', 'name' => 'Café Português', 'file' => 'https://actions.google.com/sounds/v1/ambiences/coffee_shop.ogg', 'duration' => 'Loop'],
            ],
        ],
        'rain' => [
            'name' => 'Chuva & Vento',
            'icon' => 'ri-rainy-line',
            'color' => 'slate',
            'sounds' => [
                ['id' => 'rain-porto', 'name' => 'Chuva no Porto', 'file' => 'https://actions.google.com/sounds/v1/water/rain_on_roof.ogg', 'duration' => 'Loop'],
                ['id' => 'storm', 'name' => 'Tempestade Atlântica', 'file' => 'https://actions.google.com/sounds/v1/weather/thunderstorm.ogg', 'duration' => 'Loop'],
                ['id' => 'garden-rain', 'name' => 'Chuva no Jardim', 'file' => 'https://actions.google.com/sounds/v1/weather/rain_heavy_loud.ogg', 'duration' => 'Loop'],
            ],
        ],
    ],
];