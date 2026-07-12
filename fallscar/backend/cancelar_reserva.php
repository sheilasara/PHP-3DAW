<?php

header('Content-Type: application/json');
require_once "verifica_sessao.php";
require_once "conexao.php";

$dados = json_decode(file_get_contents("php://input"), true);
$reservaId = intval($dados['reserva_id'] ?? 0);
$clienteId = $_SESSION['cliente_id'];

if (!$reservaId) {
    echo json_encode(["sucesso" => false, "mensagem" => "Reserva invalida."]);
    exit;
}

$sql = "SELECT id, veiculo_id, data_inicio, status FROM reservas
        WHERE id = :id AND cliente_id = :cliente_id";
$stmt = $conexao->prepare($sql);
$stmt->execute([":id" => $reservaId, ":cliente_id" => $clienteId]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    echo json_encode(["sucesso" => false, "mensagem" => "Reserva nao encontrada."]);
    exit;
}

if ($reserva['status'] === 'cancelada') {
    echo json_encode(["sucesso" => false, "mensagem" => "Esta reserva ja esta cancelada."]);
    exit;
}

$agora = new DateTime();
$inicioReserva = new DateTime($reserva['data_inicio']);
$diferencaHoras = ($inicioReserva->getTimestamp() - $agora->getTimestamp()) / 3600;

if ($diferencaHoras < 24) {
    echo json_encode(["sucesso" => false, "mensagem" => "So e possivel cancelar ate 24 horas antes da retirada."]);
    exit;
}

$conexao->beginTransaction();
try {
    $sql = "UPDATE reservas SET status = 'cancelada' WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([":id" => $reservaId]);

    $sql = "UPDATE veiculos SET disponivel = 1 WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([":id" => $reserva['veiculo_id']]);

    $conexao->commit();
    echo json_encode(["sucesso" => true, "mensagem" => "Reserva cancelada com sucesso."]);
} catch (Exception $e) {
    $conexao->rollBack();
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao cancelar a reserva."]);
}
