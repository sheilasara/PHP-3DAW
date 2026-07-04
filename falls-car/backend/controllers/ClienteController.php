<?php
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../helpers/Response.php';

/**
 * Controlador da área de perfil do cliente autenticado.
 */
class ClienteController
{
    private Cliente $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new Cliente();
    }

    /** GET /cliente/perfil */
    public function perfil(int $idCliente): void
    {
        $cliente = $this->clienteModel->buscarPorId($idCliente);

        if (!$cliente) {
            Response::erro('Cliente não encontrado.', 404);
        }

        Response::sucesso($cliente);
    }

    /** PUT /cliente/perfil */
    public function atualizarPerfil(int $idCliente, array $corpo): void
    {
        $this->clienteModel->atualizarPerfil($idCliente, $corpo);
        $cliente = $this->clienteModel->buscarPorId($idCliente);

        Response::sucesso($cliente, 'Perfil atualizado com sucesso.');
    }
}
