<?php
require_once __DIR__ . '/../config/database.php';

class Reserva
{
    private PDO $pdo;

    public const PERIODOS_VALIDOS = [7, 15, 30];
    public const HORAS_LIMITE_CANCELAMENTO = 24;
    public const VALOR_MOTORISTA_ADICIONAL = 50.00;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function criar(array $dados): array
    {
        if (!in_array((int) $dados['periodo_dias'], self::PERIODOS_VALIDOS, true)) {
            throw new InvalidArgumentException('Período de locação inválido. Escolha 7, 15 ou 30 dias.');
        }

        $this->pdo->beginTransaction();
        try {
            $veiculoStmt = $this->pdo->prepare(
                'SELECT v.id_veiculo, v.valor_diaria, v.status, v.id_loja, l.cidade
                 FROM veiculos v INNER JOIN lojas l ON l.id_loja = v.id_loja
                 WHERE v.id_veiculo = :id FOR UPDATE'
            );
            $veiculoStmt->execute(['id' => $dados['id_veiculo']]);
            $veiculo = $veiculoStmt->fetch();

            if (!$veiculo) {
                throw new RuntimeException('Veículo não encontrado.');
            }
            if ($veiculo['status'] !== 'disponivel') {
                throw new RuntimeException('Veículo indisponível para reserva no momento.');
            }

            $lojaDevolucaoStmt = $this->pdo->prepare('SELECT cidade FROM lojas WHERE id_loja = :id');
            $lojaDevolucaoStmt->execute(['id' => $dados['id_loja_devolucao']]);
            $lojaDevolucao = $lojaDevolucaoStmt->fetch();

            if (!$lojaDevolucao) {
                throw new RuntimeException('Loja de devolução não encontrada.');
            }

            if ($lojaDevolucao['cidade'] !== $veiculo['cidade']) {
                throw new RuntimeException(
                    'A devolução deve ocorrer em uma loja da mesma cidade da retirada (' . $veiculo['cidade'] . ').'
                );
            }

            $periodoDias = (int) $dados['periodo_dias'];
            $dataInicio = new DateTime($dados['data_inicio_prevista']);
            $dataFim = (clone $dataInicio)->modify("+{$periodoDias} days");

            $valorDiaria = (float) $veiculo['valor_diaria'];
            $valorVeiculo = $valorDiaria * $periodoDias;
            $qtdMotoristas = (int) ($dados['qtd_motoristas_adicionais'] ?? 0);
            $valorMotoristas = $qtdMotoristas * self::VALOR_MOTORISTA_ADICIONAL;
            $valorTotal = $valorVeiculo + $valorMotoristas;

            $reservaStmt = $this->pdo->prepare(
                'INSERT INTO reservas
                    (id_cliente, id_veiculo, id_loja_retirada, id_loja_devolucao, periodo_dias,
                     data_inicio_prevista, data_fim_prevista, valor_diaria_aplicada,
                     valor_motorista_extra, valor_total, status)
                 VALUES
                    (:id_cliente, :id_veiculo, :id_loja_retirada, :id_loja_devolucao, :periodo_dias,
                     :data_inicio, :data_fim, :valor_diaria, :valor_motorista, :valor_total, "pendente_pagamento")'
            );
            $reservaStmt->execute([
                'id_cliente'        => $dados['id_cliente'],
                'id_veiculo'        => $veiculo['id_veiculo'],
                'id_loja_retirada'  => $veiculo['id_loja'],
                'id_loja_devolucao' => $dados['id_loja_devolucao'],
                'periodo_dias'      => $periodoDias,
                'data_inicio'       => $dataInicio->format('Y-m-d H:i:s'),
                'data_fim'          => $dataFim->format('Y-m-d H:i:s'),
                'valor_diaria'      => $valorDiaria,
                'valor_motorista'   => $valorMotoristas,
                'valor_total'       => $valorTotal,
            ]);

            $idReserva = (int) $this->pdo->lastInsertId();

            // Motorista adicional é opcional; a lista de motoristas vem em
            // $dados['motoristas'] com nome/cpf/cnh de cada um.
            if (!empty($dados['motoristas']) && is_array($dados['motoristas'])) {
                $motoristaStmt = $this->pdo->prepare(
                    'INSERT INTO motoristas_adicionais (id_reserva, nome, cpf, cnh, valor_extra)
                     VALUES (:id_reserva, :nome, :cpf, :cnh, :valor_extra)'
                );
                foreach ($dados['motoristas'] as $motorista) {
                    $motoristaStmt->execute([
                        'id_reserva'  => $idReserva,
                        'nome'        => $motorista['nome'],
                        'cpf'         => $motorista['cpf'],
                        'cnh'         => $motorista['cnh'],
                        'valor_extra' => self::VALOR_MOTORISTA_ADICIONAL,
                    ]);
                }
            }

            
            $this->pdo->prepare('UPDATE veiculos SET status = "reservado" WHERE id_veiculo = :id')
                ->execute(['id' => $veiculo['id_veiculo']]);

            $this->pdo->commit();

            return $this->buscarPorId($idReserva);
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function buscarPorId(int $idReserva): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, v.placa, v.marca, v.modelo, v.categoria,
                    lr.nome AS loja_retirada_nome, lr.cidade AS loja_retirada_cidade,
                    ld.nome AS loja_devolucao_nome
             FROM reservas r
             INNER JOIN veiculos v ON v.id_veiculo = r.id_veiculo
             INNER JOIN lojas lr ON lr.id_loja = r.id_loja_retirada
             INNER JOIN lojas ld ON ld.id_loja = r.id_loja_devolucao
             WHERE r.id_reserva = :id'
        );
        $stmt->execute(['id' => $idReserva]);
        $reserva = $stmt->fetch();
        if (!$reserva) {
            return null;
        }

        $motoristasStmt = $this->pdo->prepare(
            'SELECT nome, cpf, cnh, valor_extra FROM motoristas_adicionais WHERE id_reserva = :id'
        );
        $motoristasStmt->execute(['id' => $idReserva]);
        $reserva['motoristas_adicionais'] = $motoristasStmt->fetchAll();

        return $reserva;
    }

    public function listarPorCliente(int $idCliente, ?string $status = null): array
    {
        $sql = 'SELECT r.id_reserva, r.status, r.periodo_dias, r.data_inicio_prevista,
                       r.data_fim_prevista, r.valor_total, v.placa, v.marca, v.modelo,
                       lr.nome AS loja_retirada_nome, lr.cidade AS loja_retirada_cidade,
                       ld.nome AS loja_devolucao_nome
                FROM reservas r
                INNER JOIN veiculos v ON v.id_veiculo = r.id_veiculo
                INNER JOIN lojas lr ON lr.id_loja = r.id_loja_retirada
                INNER JOIN lojas ld ON ld.id_loja = r.id_loja_devolucao
                WHERE r.id_cliente = :id_cliente';
        $params = ['id_cliente' => $idCliente];

        if ($status !== null && $status !== '') {
            $sql .= ' AND r.status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY r.criado_em DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function confirmar(int $idReserva): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE reservas SET status = "confirmada" WHERE id_reserva = :id AND status = "pendente_pagamento"'
        );
        $stmt->execute(['id' => $idReserva]);
    }

    public function cancelar(int $idReserva, int $idCliente, ?string $motivo = null): void
    {
        $reserva = $this->buscarPorId($idReserva);

        if (!$reserva || (int) $reserva['id_cliente'] !== $idCliente) {
            throw new RuntimeException('Reserva não encontrada para este cliente.');
        }

        if (in_array($reserva['status'], ['cancelada', 'concluida', 'em_andamento'], true)) {
            throw new RuntimeException('Esta reserva não pode mais ser cancelada (status atual: ' . $reserva['status'] . ').');
        }

        $agora = new DateTime();
        $inicioPrevisto = new DateTime($reserva['data_inicio_prevista']);
        $horasRestantes = ($inicioPrevisto->getTimestamp() - $agora->getTimestamp()) / 3600;

        if ($horasRestantes < self::HORAS_LIMITE_CANCELAMENTO) {
            throw new RuntimeException(
                'Cancelamento não permitido: o prazo mínimo é de ' . self::HORAS_LIMITE_CANCELAMENTO .
                'h antes da retirada prevista.'
            );
        }

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare(
                'UPDATE reservas SET status = "cancelada", motivo_cancelamento = :motivo WHERE id_reserva = :id'
            )->execute(['motivo' => $motivo, 'id' => $idReserva]);

            $this->pdo->prepare('UPDATE veiculos SET status = "disponivel" WHERE id_veiculo = :id')
                ->execute(['id' => $reserva['id_veiculo']]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function confirmarRetirada(int $idReserva, int $idCliente, int $kmRetirada): array
    {
        $reserva = $this->buscarPorId($idReserva);

        if (!$reserva || (int) $reserva['id_cliente'] !== $idCliente) {
            throw new RuntimeException('Reserva não encontrada para este cliente.');
        }

        if ($reserva['status'] !== 'confirmada') {
            throw new RuntimeException('Só é possível retirar o veículo de uma reserva confirmada (pagamento aprovado).');
        }

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare(
                'INSERT INTO locacoes (id_reserva, data_retirada_real, km_retirada, status)
                 VALUES (:id_reserva, NOW(), :km, "em_andamento")'
            )->execute(['id_reserva' => $idReserva, 'km' => $kmRetirada]);

            $this->pdo->prepare('UPDATE reservas SET status = "em_andamento" WHERE id_reserva = :id')
                ->execute(['id' => $idReserva]);

            $this->pdo->prepare('UPDATE veiculos SET status = "alugado" WHERE id_veiculo = :id')
                ->execute(['id' => $reserva['id_veiculo']]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return $this->buscarPorId($idReserva);
    }

    public function confirmarDevolucao(int $idReserva, int $idCliente, int $kmDevolucao): array
    {
        $reserva = $this->buscarPorId($idReserva);

        if (!$reserva || (int) $reserva['id_cliente'] !== $idCliente) {
            throw new RuntimeException('Reserva não encontrada para este cliente.');
        }

        if ($reserva['status'] !== 'em_andamento') {
            throw new RuntimeException('Só é possível devolver um veículo cuja locação está em andamento.');
        }

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare(
                'UPDATE locacoes SET data_devolucao_real = NOW(), km_devolucao = :km, status = "finalizada"
                 WHERE id_reserva = :id_reserva'
            )->execute(['km' => $kmDevolucao, 'id_reserva' => $idReserva]);

            $this->pdo->prepare('UPDATE reservas SET status = "concluida" WHERE id_reserva = :id')
                ->execute(['id' => $idReserva]);

            $this->pdo->prepare('UPDATE veiculos SET status = "disponivel", quilometragem = :km WHERE id_veiculo = :id')
                ->execute(['km' => $kmDevolucao, 'id' => $reserva['id_veiculo']]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return $this->buscarPorId($idReserva);
    }
}
