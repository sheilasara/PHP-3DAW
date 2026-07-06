<?php
require_once __DIR__ . '/../config/database.php';

class Cliente
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM clientes WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $cliente = $stmt->fetch();
        return $cliente ?: null;
    }

    public function buscarPorCpf(string $cpf): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM clientes WHERE cpf = :cpf');
        $stmt->execute(['cpf' => $cpf]);
        $cliente = $stmt->fetch();
        return $cliente ?: null;
    }

    public function buscarPorId(int $idCliente): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id_cliente, c.nome, c.cpf, c.cnh, c.email, c.telefone, c.endereco,
                    c.id_loja_padrao, l.nome AS loja_padrao_nome, l.cidade AS loja_padrao_cidade
             FROM clientes c
             LEFT JOIN lojas l ON l.id_loja = c.id_loja_padrao
             WHERE c.id_cliente = :id'
        );
        $stmt->execute(['id' => $idCliente]);
        $cliente = $stmt->fetch();
        return $cliente ?: null;
    }

    public function criar(array $dados): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO clientes (nome, cpf, cnh, email, senha_hash, telefone, endereco, id_loja_padrao)
             VALUES (:nome, :cpf, :cnh, :email, :senha_hash, :telefone, :endereco, :id_loja_padrao)'
        );
        $stmt->execute([
            'nome'           => $dados['nome'],
            'cpf'            => $dados['cpf'],
            'cnh'            => $dados['cnh'],
            'email'          => $dados['email'],
            'senha_hash'     => password_hash($dados['senha'], PASSWORD_BCRYPT),
            'telefone'       => $dados['telefone'] ?? null,
            'endereco'       => $dados['endereco'] ?? null,
            'id_loja_padrao' => $dados['id_loja_padrao'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function atualizarPerfil(int $idCliente, array $dados): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE clientes SET telefone = :telefone, endereco = :endereco, id_loja_padrao = :id_loja_padrao
             WHERE id_cliente = :id'
        );
        $stmt->execute([
            'telefone'       => $dados['telefone'] ?? null,
            'endereco'       => $dados['endereco'] ?? null,
            'id_loja_padrao' => $dados['id_loja_padrao'] ?? null,
            'id'             => $idCliente,
        ]);
    }

    public function criarSessao(int $idCliente, string $token, string $expiraEm): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sessoes (id_cliente, token, expira_em) VALUES (:id_cliente, :token, :expira_em)'
        );
        $stmt->execute([
            'id_cliente' => $idCliente,
            'token'      => $token,
            'expira_em'  => $expiraEm,
        ]);
    }

    public function encerrarSessao(string $token): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessoes WHERE token = :token');
        $stmt->execute(['token' => $token]);
    }
}
