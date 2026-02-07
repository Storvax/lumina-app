<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Room::create([
            'name' => 'Ansiedade Social',
            'slug' => 'ansiedade',
            'description' => 'Sentes o coração a bater rápido antes de sair? Aqui todos te entendem.',
            'color' => 'indigo',
            'icon' => 'ri-thunderstorms-line',
        ]);

        \App\Models\Room::create([
            'name' => 'Luto e Perda',
            'slug' => 'luto',
            'description' => 'Um espaço silencioso e respeitoso para processares a tua dor.',
            'color' => 'slate',
            'icon' => 'ri-candle-line',
        ]);

        \App\Models\Room::create([
            'name' => 'Depressão',
            'slug' => 'depressao',
            'description' => 'Para os dias cinzentos onde sair da cama é uma vitória.',
            'color' => 'blue',
            'icon' => 'ri-rainy-line',
        ]);

        \App\Models\Room::create([
            'name' => 'Stress no Trabalho',
            'slug' => 'trabalho',
            'description' => 'Burnout, chefes tóxicos e prazos impossíveis. Desabafa aqui.',
            'color' => 'rose',
            'icon' => 'ri-briefcase-line',
        ]);

        \App\Models\Room::create([
            'name' => 'Off-Topic & Memes',
            'slug' => 'off-topic',
            'description' => 'Porque rir também é o melhor remédio. Conversas leves.',
            'color' => 'teal',
            'icon' => 'ri-emotion-laugh-line',
        ]);
    }
}
