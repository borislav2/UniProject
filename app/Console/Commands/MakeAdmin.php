<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdmin extends Command
{
    protected $signature = 'creatium:make-admin {email} {--name=Admin}';

    protected $description = 'Create (or promote) an administrator account for the admin panel';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Невалиден имейл адрес.');

            return self::FAILURE;
        }

        if (! Role::where('slug', 'admin')->exists()) {
            $this->call('db:seed', ['--class' => RoleSeeder::class, '--force' => true]);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $password = $this->secret('Парола (минимум 12 символа)');

            if (strlen((string) $password) < 12) {
                $this->error('Паролата трябва да е поне 12 символа.');

                return self::FAILURE;
            }

            $user = User::create([
                'name' => $this->option('name'),
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        }

        $user->roles()->syncWithoutDetaching([Role::where('slug', 'admin')->value('id')]);

        $this->info("Готово: {$email} е администратор.");

        return self::SUCCESS;
    }
}
