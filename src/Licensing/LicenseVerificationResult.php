<?php

namespace SolutionForest\InspireCms\Licensing;

class LicenseVerificationResult
{
    /**
     * @param  bool  $isSuccess  Whether the license verification was successful
     * @param  string  $message  The verification message or error details
     * @param  bool  $isOnline  Whether the license verification was performed online
     */
    public function __construct(
        protected bool $isSuccess,
        protected string $message = '',
        protected bool $isOnline = false
    ) {}

    /**
     * Check if the license verification was successful.
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * Get the verification message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Create a successful online verification result.
     */
    public static function successOnline(?string $message = null): static
    {
        return new static(true, $message ?? 'License is valid (online)', true);
    }

    /**
     * Create a successful offline verification result.
     */
    public static function successOffline(?string $message = null): static
    {
        return new static(true, $message ?? 'License is valid (offline)', false);
    }

    /**
     * Create a failed online verification result.
     */
    public static function failureOnline(string $message): static
    {
        return new static(false, $message, true);
    }

    /**
     * Create a failed offline verification result.
     */
    public static function failureOffline(string $message): static
    {
        return new static(false, $message, false);
    }

    /**
     * Convert the result to an array.
     */
    public function toArray(): array
    {
        return [
            'success' => $this->isSuccess,
            'message' => $this->message,
        ];
    }
}
