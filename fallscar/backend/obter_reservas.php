<?php

header('Content-Type: application/json');
require_once "verifica_sessao.php";
require_once "conexao.php";

$clienteId = $_SESSION['cliente_id'];

$sql = "SELECT r.id, r.data_inicio, r.data_fim, r.periodo_dias, r.valor_total, r.status,
               v.modelo, v.marca, v.imagem,
               lr.nome AS loja_retirada, ld.nome AS loja_devolucao
        FROM reservas r
        INNER JOIN veiculos v ON r.veiculo_id = v.id
        INNER JOIN lojas lr ON r.loja_retirada_id = lr.id
        INNER JOIN lojas ld ON r.loja_devolucao_id = ld.id
        WHERE r.cliente_id = :cliente_id
        ORDER BY r.data_reserva DESC";

$stmt = $conexao->prepare($sql);
$stmt->execute([":cliente_id" => $clienteId]);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["sucesso" => true, "reservas" => $reservas]);
