<?php

namespace Services;

class AuthService
{
    public function getBearerTokenFromHeader(array $headers): ?string
    {
        $authHeader = $headers['Authorization'] ?? null;
        if ($authHeader !== null && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function validateToken(?string $token): bool
    {
        $envToken = $_ENV['TOKEN'] ?? '';
        return $token === $envToken;
    }
}
