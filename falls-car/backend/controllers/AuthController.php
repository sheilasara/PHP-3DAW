<?php
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Auth.php';

/**
 * Controlador de autenticação: cadastro, login e logout de clientes.
 */
class AuthController
{
    private Cliente $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new Cliente();
    }

    /** POST /cadastro */
    public function cadastrar(array $corpo): void
    {
        $obrigatorios = ['nome', 'cpf', 'cnh', 'email', 'senha'];
        foreach ($obrigatorios as $campo) {
            if (empty($corpo[$campo])) {
                Response::erro("O campo '{$campo}' é obrigatório.", 422);
            }
        }

        if (!filter_var($corpo['email'], FILTER_VALIDATE_EMAIL)) {
            Response::erro('E-mail inválido.', 422);
        }

        if (strlen($corpo['senha']) < 6) {
            Response::erro('A senha deve ter ao menos 6 caracteres.', 422);
        }

        if ($this->clienteModel->buscarPorEmail($corpo['email'])) {
            Response::erro('Já existe um cliente cadastrado com este e-mail.', 409);
        }

        if ($this->clienteModel->buscarPorCpf($corpo['cpf'])) {
            Response::erro('Já existe um cliente cadastrado com este CPF.', 409);
        }

        $idCliente = $this->clienteModel->criar($corpo);
        $cliente = $this->clienteModel->buscarPorId($idCliente);

        Response::sucesso($cliente, 'Cadastro realizado com sucesso.', 201);
    }

    /** POST /login */
    public function login(array $corpo): void
    {
        if (empty($corpo['email']) || empty($corpo['senha'])) {
            Response::erro('Informe e-mail e senha.', 422);
        }

        $cliente = $this->clienteModel->buscarPorEmail($corpo['email']);

        if (!$cliente || !password_verify($corpo['senha'], $cliente['senha_hash'])) {
            Response::erro('E-mail ou senha inválidos.', 401);
        }

        $token = Auth::gerarToken();
        $expiraEm = (new DateTime('+8 hours'))->format('Y-m-d H:i:s');
        $this->clienteModel->criarSessao((int) $cliente['id_cliente'], $token, $expiraEm);

        unset($cliente['senha_hash']);

        Response::sucesso([
            'token'   => $token,
            'expira_em' => $expiraEm,
            'cliente' => $cliente,
        ], 'Login realizado com sucesso.');
    }

    /** POST /logout */
    public function logout(): void
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (preg_match('/Bearer\s+(\S+)/', $authHeader, $match)) {
            $this->clienteModel->encerrarSessao($match[1]);
        }

        Response::sucesso(null, 'Sessão encerrada com sucesso.');
    }
}
