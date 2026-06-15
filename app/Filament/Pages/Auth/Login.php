<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\LoginAudit;
use App\Models\User;
use App\Services\Notifications\CrmNotificationService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('username')
            ->label('Username')
            ->required()
            ->autocomplete('username')
            ->autofocus();
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $response = parent::authenticate();
        } catch (ValidationException $exception) {
            $this->handleFailedLoginAttempt();

            throw $exception;
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            return $response;
        }

        if (! $user->isActive()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'data.username' => 'Your account is not active.',
            ]);
        }

        $user->forceFill([
            'failed_login_attempts' => 0,
            'last_login_at' => now(),
        ])->save();

        LoginAudit::query()->create([
            'user_id' => $user->id,
            'ip_address' => request()->ip() ?? 'unknown',
            'user_agent' => request()->userAgent(),
            'logged_in_at' => now(),
        ]);

        app(CrmNotificationService::class)->notifyOverdueTasksOnLogin($user);

        return $response;
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }

    private function handleFailedLoginAttempt(): void
    {
        $username = $this->form->getState()['username'] ?? null;

        if (! is_string($username) || $username === '') {
            return;
        }

        $user = User::query()
            ->where('username', $username)
            ->first();

        if (! $user instanceof User) {
            return;
        }

        $failedAttempts = $user->failed_login_attempts + 1;

        $data = [
            'failed_login_attempts' => $failedAttempts,
        ];

        if ($failedAttempts >= 10) {
            $data['status'] = User::STATUS_LOCKED;
            $data['locked_at'] = now();
        }

        $user->forceFill($data)->save();
    }
}
