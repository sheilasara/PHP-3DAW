<?php

header('Content-Type: application/json');
require_once "verifica_sessao.php";
require_once "conexao.php";

$clienteId = $_SESSION['cliente_id'];
$reservaId = intval($_GET['id'] ?? 0);

if (!$reservaId) {
    echo json_encode(["sucesso" => false, "mensagem" => "Reserva invalida."]);
    exit;
}

$sql = "SELECT r.*, v.modelo, v.marca, v.categoria, v.imagem, v.valor_diaria,
               lr.nome AS loja_retirada, lr.cidade AS cidade_retirada,
               ld.nome AS loja_devolucao
        FROM reservas r
        INNER JOIN veiculos v ON r.veiculo_id = v.id
        INNER JOIN lojas lr ON r.loja_retirada_id = lr.id
        INNER JOIN lojas ld ON r.loja_devolucao_id = ld.id
        WHERE r.id = :id AND r.cliente_id = :cliente_id";
$stmt = $conexao->prepare($sql);
$stmt->execute([":id" => $reservaId, ":cliente_id" => $clienteId]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    echo json_encode(["sucesso" => false, "mensagem" => "Reserva nao encontrada."]);
    exit;
}

$sql = "SELECT nome, cpf, habilitacao FROM motoristas WHERE reserva_id = :id";
$stmt = $conexao->prepare($sql);
$stmt->execute([":id" => $reservaId]);
$motorista = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT metodo, numero_cartao, valor, status, data_pagamento FROM pagamentos WHERE reserva_id = :id";
$stmt = $conexao->prepare($sql);
$stmt->execute([":id" => $reservaId]);
$pagamento = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "sucesso" => true,
    "reserva" => $reserva,
    "motorista" => $motorista ?: null,
    "pagamento" => $pagamento ?: null
]);
