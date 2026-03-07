<?php

namespace Database\Seeders;

use App\Models\Therapist;
use Illuminate\Database\Seeder;

class TherapistSeeder extends Seeder
{
    public function run(): void
    {
        $therapists = [
            [
                'name' => 'Dra. Sofia Almeida',
                'specialty' => 'Ansiedade e Perturbações de Pânico',
                'approach' => 'Terapia Cognitivo-Comportamental (TCC)',
                'avatar' => 'therapists/sofia.jpg',
            ],
            [
                'name' => 'Dr. Miguel Santos',
                'specialty' => 'Depressão e Burnout',
                'approach' => 'Terapia Focada na Compaixão (CFT)',
                'avatar' => 'therapists/miguel.jpg',
            ],
            [
                'name' => 'Dra. Inês Costa',
                'specialty' => 'Trauma e PTSD',
                'approach' => 'EMDR e Terapia Somática',
                'avatar' => 'therapists/ines.jpg',
            ],
            [
                'name' => 'Dr. Tiago Ferreira',
                'specialty' => 'Relações Interpessoais e Luto',
                'approach' => 'Terapia Psicodinâmica',
                'avatar' => 'therapists/tiago.jpg',
            ],
            [
                'name' => 'Dra. Ana Rodrigues',
                'specialty' => 'TDAH e Neurodivergência',
                'approach' => 'Terapia Integrativa',
                'avatar' => 'therapists/ana.jpg',
            ],
        ];

        foreach ($therapists as $therapist) {
            Therapist::firstOrCreate(
                ['name' => $therapist['name']],
                $therapist
            );
        }
    }
}
