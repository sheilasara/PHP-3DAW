<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Response.php';

/**
 * Middleware simples de autenticação baseado em token de sessão.
 *
 * Fluxo: no login, um token aleatório é gerado e salvo na tabela
 * "sessoes" com validade de algumas horas. O frontend deve enviar esse
 * token em toda requisição autenticada no cabeçalho:
 *   Authorization: Bearer <token>
 */
class Auth
{
    /**
     * Valida o token do cabeçalho Authorization e retorna o id_cliente
     * correspondente. Encerra a requisição com erro 401 se inválido.
     */
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

        // Fallback para servidores onde getallheaders() não existe (ex.: alguns PHP-FPM/Nginx).
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
