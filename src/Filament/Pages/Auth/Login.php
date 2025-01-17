<?php

namespace SolutionForest\InspireCms\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BasePage;
use Illuminate\Auth\Events as AuthEvents;
use SolutionForest\InspireCms\Filament\Pages\Auth\Concerns\HaveBackgroundImage;

class Login extends BasePage
{
    use HaveBackgroundImage;

    /**
     * @var view-string
     */
    protected static string $view = 'inspirecms::filament.pages.auth.login';

    /**
     * @var view-string
     */
    protected static string $layout = 'inspirecms::components.layout.split-image-login-page';

    protected ?string $maxWidth = '4xl';

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {

            // Already handled
            // event(new AuthEvents\Failed(Filament::getAuthGuard(), null, $this->getCredentialsFromFormData($data)));

            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        event(new AuthEvents\Login(Filament::getAuthGuard(), $user, true));

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }

    protected function getRememberFormComponent(): \Filament\Forms\Components\Component
    {
        return parent::getRememberFormComponent()->default(true);
    }
}
