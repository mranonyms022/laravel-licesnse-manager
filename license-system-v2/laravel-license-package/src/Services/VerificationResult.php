<?php

namespace YourVendor\LaravelLicense\Services;

class VerificationResult
{
    public function __construct(
        public readonly bool   $valid,
        public readonly bool   $inGracePeriod,
        public readonly string $reason,
        public readonly array  $payload = [],
        public readonly array  $meta    = [],
    ) {}

    public static function valid(array $payload): self
    {
        return new self(
            valid:         true,
            inGracePeriod: false,
            reason:        'OK',
            payload:       $payload,
        );
    }

    public static function grace(array $payload): self
    {
        return new self(
            valid:         true,
            inGracePeriod: true,
            reason:        'GRACE_PERIOD',
            payload:       $payload,
        );
    }

    public static function fail(string $reason, array $meta = []): self
    {
        return new self(
            valid:         false,
            inGracePeriod: false,
            reason:        $reason,
            meta:          $meta,
        );
    }

    public function clientName(): string
    {
        return $this->payload['cli'] ?? '';
    }

    public function domain(): string
    {
        return $this->payload['dom'] ?? '';
    }

    public function expiresAt(): \DateTimeImmutable|null
    {
        if (isset($this->payload['exp'])) {
            return (new \DateTimeImmutable())->setTimestamp($this->payload['exp']);
        }
        return null;
    }

    public function features(): array
    {
        return $this->payload['ftr'] ?? [];
    }
}
