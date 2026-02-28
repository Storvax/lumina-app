<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Cria o utilizador administrador inicial';

    public function handle()
    {
        User::firstOrCreate(
            ['email' => 'alex@lumina.pt'],
            [
                'name'              => 'Alexandre Admin',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->info('Admin criado ou já existia.');
    }
}