<?php

namespace App\Core;

/**
 * Small, dependency-free validation helper.
 */
class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, ?string $label = null): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!isset($this->data[$field]) || trim((string) $this->data[$field]) === '') {
            $this->errors[$field][] = "{$label} is required.";
        }
        return $this;
    }

    public function email(string $field): self
    {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = 'A valid email address is required.';
        }
        return $this;
    }

    public function minLength(string $field, int $len): self
    {
        if (!empty($this->data[$field]) && strlen((string) $this->data[$field]) < $len) {
            $this->errors[$field][] = ucfirst($field) . " must be at least {$len} characters.";
        }
        return $this;
    }

    public function numeric(string $field): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '' && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = ucfirst($field) . ' must be a number.';
        }
        return $this;
    }

    public function matches(string $field, string $otherField, ?string $message = null): self
    {
        if (($this->data[$field] ?? null) !== ($this->data[$otherField] ?? null)) {
            $this->errors[$field][] = $message ?: 'Fields do not match.';
        }
        return $this;
    }

    public function date(string $field): self
    {
        if (!empty($this->data[$field]) && strtotime((string) $this->data[$field]) === false) {
            $this->errors[$field][] = ucfirst($field) . ' must be a valid date.';
        }
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

    public function firstErrorMessage(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0];
        }
        return null;
    }
}
