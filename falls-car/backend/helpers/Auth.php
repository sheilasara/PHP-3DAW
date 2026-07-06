<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Response.php';

class Auth
{
    public static function clienteAutenticado(): int
    {
        $headers = self::getHeaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!preg_match('/Bearer\s+(\S+)/', $authHeader, $match)) {
            Response::erro('Token de autenticação não informado.', 401);
        }

        $token = $match[1];
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare(
            'SELECT id_cliente FROM sessoes WHERE token = :token AND expira_em > NOW()'
        );
        $stmt->execute(['token' => $token]);
        $sessao = $stmt->fetch();

        if (!$sessao) {
            Response::erro('Sessão inválida ou expirada. Faça login novamente.', 401);
        }

        return (int) $sessao['id_cliente'];
    }

    private static function getHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    public static function gerarToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
