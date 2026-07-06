<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Reserva.php';

class Pagamento
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function processar(int $idReserva, int $idCliente, string $formaPagamento): array
    {
        $reservaModel = new Reserva();
        $reserva = $reservaModel->buscarPorId($idReserva);

        if (!$reserva || (int) $reserva['id_cliente'] !== $idCliente) {
            throw new RuntimeException('Reserva não encontrada para este cliente.');
        }

        if ($reserva['status'] !== 'pendente_pagamento') {
            throw new RuntimeException('Esta reserva não está aguardando pagamento (status atual: ' . $reserva['status'] . ').');
        }

        $formasValidas = ['cartao_credito', 'cartao_debito', 'pix'];
        if (!in_array($formaPagamento, $formasValidas, true)) {
            throw new InvalidArgumentException('Forma de pagamento inválida.');
        }

        $this->pdo->beginTransaction();
        try {
            
            $stmt = $this->pdo->prepare(
                'INSERT INTO pagamentos (id_reserva, valor, forma_pagamento, status)
                 VALUES (:id_reserva, :valor, :forma, "aprovado")'
            );
            $stmt->execute([
                'id_reserva' => $idReserva,
                'valor'      => $reserva['valor_total'],
                'forma'      => $formaPagamento,
            ]);

            $reservaModel->confirmar($idReserva);

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return $reservaModel->buscarPorId($idReserva);
    }

    public function listarPorReserva(int $idReserva): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM pagamentos WHERE id_reserva = :id ORDER BY data_pagamento DESC');
        $stmt->execute(['id' => $idReserva]);
        return $stmt->fetchAll();
    }
}
