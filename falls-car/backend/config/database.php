<?php
/**
 * Configuração e conexão com o banco de dados (MySQL via PDO).
 *
 * Ajuste as constantes abaixo conforme o ambiente (local/produção).
 * Em um cenário real, estes valores viriam de variáveis de ambiente,
 * nunca de código versionado.
 */

class Database
{
    private static ?PDO $connection = null;

    private const HOST = 'localhost';
    private const DB_NAME = 'falls_car';
    private const USER = 'root';
    private const PASS = '';
    private const CHARSET = 'utf8mb4';

    /**
     * Retorna uma conexão PDO única (padrão Singleton) para toda a
     * requisição, evitando múltiplas conexões desnecessárias.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::DB_NAME . ';charset=' . self::CHARSET;

            try {
                self::$connection = new PDO($dsn, self::USER, self::PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'sucesso' => false,
                    'mensagem' => 'Falha ao conectar ao banco de dados.',
                ]);
                exit;
            }
        }

        return self::$connection;
    }
}
