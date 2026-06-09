<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Security\PasswordRules;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateGmUserCommand extends Command
{
    protected $signature = 'app:user:create-gm
        {username : Login username}
        {password : User password}
        {--name=Owner GM : Full name}
        {--telegram= : Telegram contact}';

    protected $description = 'Create the first Owner / GM user. Example: php artisan app:user:create-gm admin "StrongPass1!" --name="Owner GM"';

    public function handle(): int
    {
        $data = [
            'full_name' => (string) $this->option('name'),
            'username' => (string) $this->argument('username'),
            'password' => (string) $this->argument('password'),
            'telegram_contact' => $this->option('telegram') !== null
                ? (string) $this->option('telegram')
                : null,
        ];

        $validator = Validator::make($data, [
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:180', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'string', PasswordRules::default()],
            'telegram_contact' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::query()->create([
            'full_name' => $data['full_name'],
            'username' => $data['username'],
            'password' => $data['password'],
            'role' => User::ROLE_GM,
            'status' => User::STATUS_ACTIVE,
            'telegram_contact' => $data['telegram_contact'],
        ]);

        $this->info('Owner / GM user created successfully.');

        return self::SUCCESS;
    }
}
