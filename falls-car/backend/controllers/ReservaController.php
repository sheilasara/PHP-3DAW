<?php
require_once __DIR__ . '/../models/Reserva.php';
require_once __DIR__ . '/../models/Pagamento.php';
require_once __DIR__ . '/../helpers/Response.php';

class ReservaController
{
    private Reserva $reservaModel;
    private Pagamento $pagamentoModel;

    public function __construct()
    {
        $this->reservaModel = new Reserva();
        $this->pagamentoModel = new Pagamento();
    }

    public function criar(int $idCliente, array $corpo): void
    {
        $obrigatorios = ['id_veiculo', 'id_loja_devolucao', 'periodo_dias', 'data_inicio_prevista'];
        foreach ($obrigatorios as $campo) {
            if (empty($corpo[$campo])) {
                Response::erro("O campo '{$campo}' é obrigatório.", 422);
            }
        }

        $corpo['id_cliente'] = $idCliente;

        try {
            $reserva = $this->reservaModel->criar($corpo);
            Response::sucesso($reserva, 'Reserva criada com sucesso. Efetue o pagamento antecipado para confirmá-la.', 201);
        } catch (InvalidArgumentException $e) {
            Response::erro($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            Response::erro($e->getMessage(), 409);
        }
    }

    public function listarDoCliente(int $idCliente, array $query): void
    {
        $status = $query['status'] ?? null;
        $reservas = $this->reservaModel->listarPorCliente($idCliente, $status);
        Response::sucesso($reservas);
    }

    public function detalhar(int $idCliente, int $idReserva): void
    {
        $reserva = $this->reservaModel->buscarPorId($idReserva);

        if (!$reserva || (int) $reserva['id_cliente'] !== $idCliente) {
            Response::erro('Reserva não encontrada.', 404);
        }

        Response::sucesso($reserva);
    }

    public function pagar(int $idCliente, int $idReserva, array $corpo): void
    {
        if (empty($corpo['forma_pagamento'])) {
            Response::erro("O campo 'forma_pagamento' é obrigatório.", 422);
        }

        try {
            $reserva = $this->pagamentoModel->processar($idReserva, $idCliente, $corpo['forma_pagamento']);
            Response::sucesso($reserva, 'Pagamento aprovado. Reserva confirmada.');
        } catch (InvalidArgumentException $e) {
            Response::erro($e->getMessage(), 422);
        } catch (RuntimeException $e) {
            Response::erro($e->getMessage(), 409);
        }
    }

    public function cancelar(int $idCliente, int $idReserva, array $corpo): void
    {
        try {
            $this->reservaModel->cancelar($idReserva, $idCliente, $corpo['motivo'] ?? null);
            Response::sucesso(null, 'Reserva cancelada com sucesso.');
        } catch (RuntimeException $e) {
            Response::erro($e->getMessage(), 409);
        }
    }

    public function confirmarRetirada(int $idCliente, int $idReserva, array $corpo): void
    {
        $kmRetirada = (int) ($corpo['km_retirada'] ?? 0);

        try {
            $reserva = $this->reservaModel->confirmarRetirada($idReserva, $idCliente, $kmRetirada);
            Response::sucesso($reserva, 'Retirada confirmada. A locação está em andamento.');
        } catch (RuntimeException $e) {
            Response::erro($e->getMessage(), 409);
        }
    }

    public function confirmarDevolucao(int $idCliente, int $idReserva, array $corpo): void
    {
        $kmDevolucao = (int) ($corpo['km_devolucao'] ?? 0);

        try {
            $reserva = $this->reservaModel->confirmarDevolucao($idReserva, $idCliente, $kmDevolucao);
            Response::sucesso($reserva, 'Devolução registrada. Locação concluída.');
        } catch (RuntimeException $e) {
            Response::erro($e->getMessage(), 409);
        }
    }
}
