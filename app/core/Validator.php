<?php

declare(strict_types=1);

class Validator
{
    private array $errors = [];

    public function required(string $field, mixed $value, string $label): self
    {
        if (trim((string) $value) === '') {
            $this->errors[$field] = "{$label} is required.";
        }
        return $this;
    }

    public function email(string $field, string $value, string $label = 'Email'): self
    {
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} must be a valid email address.";
        }
        return $this;
    }

    public function pattern(string $field, string $value, string $regex, string $message): self
    {
        if ($value !== '' && !preg_match($regex, $value)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function positiveNumber(string $field, mixed $value, string $label): self
    {
        if ($value === '' || !is_numeric($value) || (float) $value < 0) {
            $this->errors[$field] = "{$label} must be a valid non-negative number.";
        }
        return $this;
    }

    public function minLength(string $field, string $value, int $min, string $label): self
    {
        if ($value !== '' && mb_strlen($value) < $min) {
            $this->errors[$field] = "{$label} must be at least {$min} characters.";
        }
        return $this;
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field] = $message;
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        return reset($this->errors) ?: '';
    }
}
