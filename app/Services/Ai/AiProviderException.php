<?php

namespace App\Services\Ai;

/**
 * Categorized AI provider failure.
 *
 * Lets the gateway decide fallback eligibility and log a sanitized
 * error category WITHOUT ever carrying secrets, keys, prompt content,
 * or raw provider response bodies in the message.
 */
class AiProviderException extends \RuntimeException
{
    public const BILLING_ERROR = 'billing_error';       // HTTP 402: billing/quota/model-access
    public const AUTH_ERROR = 'auth_error';             // HTTP 401/403
    public const MODEL_MISSING = 'model_missing';       // HTTP 404
    public const TIMEOUT = 'timeout';                   // HTTP 408 / client timeout
    public const RATE_LIMITED = 'rate_limited';         // HTTP 429
    public const SERVER_ERROR = 'server_error';         // HTTP 5xx
    public const CONNECTION_ERROR = 'connection_error'; // DNS/refused/reset
    public const INVALID_RESPONSE = 'invalid_response'; // 200 with malformed/empty content
    public const UNKNOWN = 'unknown';

    private string $category;
    private ?int $status;

    public function __construct(string $category, ?int $status = null)
    {
        $this->category = $category;
        $this->status = $status;
        parent::__construct('AI provider request failed.');
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public static function fromStatus(int $status): self
    {
        return new self(match (true) {
            $status === 402 => self::BILLING_ERROR,
            $status === 401 || $status === 403 => self::AUTH_ERROR,
            $status === 404 => self::MODEL_MISSING,
            $status === 408 => self::TIMEOUT,
            $status === 429 => self::RATE_LIMITED,
            $status >= 500 => self::SERVER_ERROR,
            default => self::UNKNOWN,
        }, $status);
    }

    public static function timeout(): self
    {
        return new self(self::TIMEOUT);
    }

    public static function connectionError(): self
    {
        return new self(self::CONNECTION_ERROR);
    }

    public static function invalidResponse(): self
    {
        return new self(self::INVALID_RESPONSE);
    }
}
