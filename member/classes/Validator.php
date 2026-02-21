<?php
/**
 * Validator - Input Validation
 * 
 * Validasi input dengan aturan yang jelas dan pesan error yang informatif.
 */

class Validator
{
    /** @var array */
    private $errors = [];

    /** @var array */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Validasi field name: minimal 2 karakter, maksimal 100.
     */
    public function validateName(string $field = 'name'): self
    {
        $value = trim($this->data[$field] ?? '');

        if ($value === '') {
            $this->errors[$field] = 'Nama wajib diisi.';
        } elseif (mb_strlen($value) < 2) {
            $this->errors[$field] = 'Nama minimal 2 karakter.';
        } elseif (mb_strlen($value) > 100) {
            $this->errors[$field] = 'Nama maksimal 100 karakter.';
        }

        return $this;
    }

    /**
     * Validasi email: format valid dan unique di database.
     */
    public function validateEmail(string $field = 'email', ?int $excludeId = null): self
    {
        $value = trim($this->data[$field] ?? '');

        if ($value === '') {
            $this->errors[$field] = 'Email wajib diisi.';
        } elseif (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Format email tidak valid.';
        } elseif (mb_strlen($value) > 255) {
            $this->errors[$field] = 'Email maksimal 255 karakter.';
        } else {
            // Cek unique di database
            $db = Database::getConnection();
            $sql = 'SELECT COUNT(*) FROM members WHERE email = :email';
            $params = [':email' => $value];

            if ($excludeId !== null) {
                $sql .= ' AND id != :id';
                $params[':id'] = $excludeId;
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            if ((int) $stmt->fetchColumn() > 0) {
                $this->errors[$field] = 'Email sudah terdaftar.';
            }
        }

        return $this;
    }

    /**
     * Validasi password: minimal 8 karakter, harus confirmed.
     */
    public function validatePassword(string $field = 'password', string $confirmField = 'password_confirmation'): self
    {
        $value = $this->data[$field] ?? '';
        $confirm = $this->data[$confirmField] ?? '';

        if ($value === '') {
            $this->errors[$field] = 'Password wajib diisi.';
        } elseif (mb_strlen($value) < 8) {
            $this->errors[$field] = 'Password minimal 8 karakter.';
        } elseif (mb_strlen($value) > 255) {
            $this->errors[$field] = 'Password maksimal 255 karakter.';
        }

        if ($value !== $confirm) {
            $this->errors[$confirmField] = 'Konfirmasi password tidak cocok.';
        }

        return $this;
    }

    /**
     * Cek apakah validasi lolos.
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Cek apakah validasi gagal.
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Ambil semua error.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Ambil error pertama.
     */
    public function getFirstError(): string
    {
        return reset($this->errors) ?: '';
    }
}
