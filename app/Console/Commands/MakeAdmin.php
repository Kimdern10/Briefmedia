<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdmin extends Command
{
    /**
     * @var string
     */
    protected $signature = 'user:make-admin
                            {email : Email address of the account}
                            {--revoke : Remove admin access instead of granting it}';

    /**
     * @var string
     */
    protected $description = 'Give an account access to the admin panel (role_as = 1), creating it if needed';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("\"{$email}\" is not a valid email address.");

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($this->option('revoke')) {
            if (! $user) {
                $this->error("No account found for {$email}.");

                return self::FAILURE;
            }

            $user->forceFill(['role_as' => 0])->save();
            $this->info("{$email} is no longer an admin.");

            return self::SUCCESS;
        }

        if (! $user) {
            if (! $this->confirm("No account found for {$email}. Create a new admin account?", true)) {
                return self::FAILURE;
            }

            $name = $this->ask('Full name');
            $password = $this->secret('Password (at least 8 characters)');

            if (blank($name) || strlen((string) $password) < 8) {
                $this->error('A name and a password of at least 8 characters are required.');

                return self::FAILURE;
            }

            $user = new User;
            $user->forceFill([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
        }

        $user->forceFill(['role_as' => 1])->save();

        $this->info("{$email} is now an admin. Log in and open ".url('/admin/dashboard'));

        return self::SUCCESS;
    }
}
