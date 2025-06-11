<?php

namespace SolutionForest\InspireCms\Licensing;

class LicenseVerificationResult
{
    /**
     * @param  bool  $isSuccess  Whether the license verification was successful
     * @param  string  $message  The verification message or error details
     * @param  ?string  $reason  Optional reason for failure, if applicable
     * @param  bool  $isOnline  Whether the license verification was performed online
     */
    public function __construct(
        protected bool $isSuccess,
        protected string $message = '',
        protected ?string $reason = null,
        protected bool $isOnline = false,
        /** @var ?array */
        protected $data = null,
    ) {
        if (! is_array($data)) {
            unset($this->data);
        }
    }

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

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Create a successful online verification result.
     */
    public static function successOnline(?string $message = null, $data = null): static
    {
        return new static(isSuccess: true, message: $message ?? 'License is valid (online)', isOnline: true, data: $data);
    }

    /**
     * Create a successful offline verification result.
     */
    public static function successOffline(?string $message = null, $data = null): static
    {
        return new static(isSuccess: true, message: $message ?? 'License is valid (offline)', isOnline: false, data: $data);
    }

    /**
     * Create a failed online verification result.
     */
    public static function failureOnline(?string $message = null, $reason = null, $data = null): static
    {
        return new static(isSuccess: false, message: $message ?? 'License verification failed (online)', reason: $reason, isOnline: true, data: $data);
    }

    /**
     * Create a failed offline verification result.
     */
    public static function failureOffline(?string $message = null, $reason = null, $data = null): static
    {
        return new static(isSuccess: false, message: $message ?? 'License verification failed (offline)', reason: $reason, isOnline: false, data: $data);
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
