<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin {email : Administrator email address}';

    protected $description = 'Create an administrator account using a password entered privately';

    public function handle(): int
    {
        $email = mb_strtolower(trim($this->argument('email')));
        if (User::where('email', $email)->exists()) {
            $this->error('That email already has an account. No account was changed.');

            return self::FAILURE;
        }

        $name = $this->ask('Administrator name');
        $password = $this->secret('Password (at least 12 characters, letters and numbers)');
        $confirmation = $this->secret('Confirm password');
        $validator = Validator::make([
            'name' => $name, 'email' => $email, 'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $user->is_admin = true;
        $user->save();
        $this->info('Administrator created. Sign in at /admin/login.');

        return self::SUCCESS;
    }
}
