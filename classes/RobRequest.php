<?php
declare(strict_types=1);

use Mlaphp\Request;

class RobRequest extends Request
{
    public function getInt(string $field, int $default = 0): int
    {
        return filter_var($this->post[$field] ?? $default, FILTER_VALIDATE_INT) ?: $default;
    }

    public function getString(string $field, string $default = ''): string
    {
        return trim($this->post[$field] ?? $default);
    }

    public function getAction(string $default = ''): string
    {
        return $this->getString('action', $default);
    }

    public function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }

    public function jsonSuccess(array $data = []): void
    {
        echo json_encode(['success' => true] + $data);
    }

    public function requireFields(array $fields): void
    {
        foreach ($fields as $field => $condition) {
            if (!$condition) {
                $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required');
            }
        }
    }
}
