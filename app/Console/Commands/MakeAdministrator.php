<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeAdministrator extends Command
{
    protected $signature = 'app:make-admin {email : Email address for the administrator} {--name= : Name when creating a new administrator}';

    protected $description = 'Create an administrator account or grant administrator access to an existing account';

    public function handle(): int
    {
        $email = $this->argument('email');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->components->error('Enter a valid email address.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->forceFill(['role' => 'admin'])->save();
            $this->components->info("Administrator access granted to {$user->email}.");

            return self::SUCCESS;
        }

        if (! $this->input->isInteractive()) {
            $this->components->error('Account not found. Run this command interactively to create the first administrator.');

            return self::FAILURE;
        }

        $name = $this->option('name') ?: $this->ask('Name for the new administrator');
        $password = $this->secret('Password (at least 8 characters)');
        $confirmation = $this->secret('Confirm password');

        if (! is_string($name) || trim($name) === '' || ! is_string($password) || strlen($password) < 8 || $password !== $confirmation) {
            $this->components->error('A name and matching password of at least 8 characters are required.');

            return self::FAILURE;
        }

        $usernameBase = Str::slug(Str::before($email, '@'), '_') ?: 'administrator';
        $username = $usernameBase;
        for ($suffix = 2; User::where('username', $username)->exists(); $suffix++) {
            $username = $usernameBase.'_'.$suffix;
        }

        $user = User::create([
            'name' => trim($name),
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
        ]);
        $this->components->info("Administrator account created for {$user->email}.");

        return self::SUCCESS;
    }
}
