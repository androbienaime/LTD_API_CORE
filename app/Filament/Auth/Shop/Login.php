<?php

namespace App\Filament\Auth\Shop;

use App\Models\Core\Account;
use Filament\Pages\Auth\Login as BaseLogin;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
            'type' => 'merchant',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        // Vérifier d'abord si l'utilisateur existe et est un marchand
        $user = Account::where('email', $data['email'])->first();

        if (!$user) {
            $this->throwFailureValidationException();
        }

        if ($user->type !== 'merchant') {
            throw ValidationException::withMessages([
                'data.email' => __('Vous n\'avez pas les permissions de type marchand requises.'),
            ]);
        }

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('Ces identifiants ne correspondent pas à nos enregistrements.'),
        ]);
    }
}
