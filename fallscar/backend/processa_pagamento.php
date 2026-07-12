<?php


header('Content-Type: application/json');
require_once "verifica_sessao.php";
require_once "conexao.php";

$dados = json_decode(file_get_contents("php://input"), true);

$clienteId = $_SESSION['cliente_id'];
$reservaId = intval($dados['reserva_id'] ?? 0);
$metodo = trim($dados['metodo'] ?? '');
$nomeImpresso = trim($dados['nome_impresso'] ?? '');
$numeroCartao = trim($dados['numero_cartao'] ?? '');
$validade = trim($dados['validade'] ?? '');
$cvv = trim($dados['cvv'] ?? '');

if (!$reservaId || empty($metodo)) {
    echo json_encode(["sucesso" => false, "mensagem" => "Dados de pagamento incompletos."]);
    exit;
}


$sql = "SELECT id, valor_total, status FROM reservas WHERE id = :id AND cliente_id = :cliente_id";
$stmt = $conexao->prepare($sql);
$stmt->execute([":id" => $reservaId, ":cliente_id" => $clienteId]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    echo json_encode(["sucesso" => false, "mensagem" => "Reserva nao encontrada."]);
    exit;
}

if ($reserva['status'] !== 'pendente') {
    echo json_encode(["sucesso" => false, "mensagem" => "Esta reserva ja foi paga ou cancelada."]);
    exit;
}


if ($metodo === 'cartao') {
    if (empty($nomeImpresso) || empty($numeroCartao) || empty($validade) || empty($cvv)) {
        echo json_encode(["sucesso" => false, "mensagem" => "Preencha todos os campos do cartao."]);
        exit;
    }

    
    $numeroLimpo = preg_replace('/\D/', '', $numeroCartao);
    if (strlen($numeroLimpo) < 13 || strlen($numeroLimpo) > 19) {
        echo json_encode(["sucesso" => false, "mensagem" => "Numero do cartao invalido."]);
        exit;
    }

    
    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $validade)) {
        echo json_encode(["sucesso" => false, "mensagem" => "Validade invalida. Use o formato MM/AAAA."]);
        exit;
    }
    list($mesValidade, $anoValidade) = explode('/', $validade);
    $dataValidade = DateTime::createFromFormat('Y-m-d', "$anoValidade-$mesValidade-01");
    $dataValidade->modify('last day of this month');
    if ($dataValidade < new DateTime()) {
        echo json_encode(["sucesso" => false, "mensagem" => "Cartao vencido."]);
        exit;
    }


    if (!preg_match('/^\d{3,4}$/', $cvv)) {
        echo json_encode(["sucesso" => false, "mensagem" => "CVV invalido."]);
        exit;
    }


    $numeroCartaoSalvo = "**** **** **** " . substr($numeroLimpo, -4);

} elseif ($metodo === 'pix') {
   
    $nomeImpresso = null;
    $numeroCartaoSalvo = null;
    $validade = null;
} else {
    echo json_encode(["sucesso" => false, "mensagem" => "Metodo de pagamento invalido."]);
    exit;
}


$conexao->beginTransaction();
try {
    $sql = "INSERT INTO pagamentos (reserva_id, metodo, nome_impresso, numero_cartao, validade_cartao, valor, status)
            VALUES (:reserva_id, :metodo, :nome_impresso, :numero_cartao, :validade, :valor, 'aprovado')";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([
        ":reserva_id" => $reservaId,
        ":metodo" => $metodo,
        ":nome_impresso" => $nomeImpresso,
        ":numero_cartao" => $numeroCartaoSalvo ?? null,
        ":validade" => $validade,
        ":valor" => $reserva['valor_total']
    ]);

    
    $sql = "UPDATE reservas SET status = 'confirmada' WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([":id" => $reservaId]);

    $conexao->commit();
    echo json_encode(["sucesso" => true, "mensagem" => "Pagamento aprovado! Sua reserva esta confirmada."]);
} catch (Exception $e) {
    $conexao->rollBack();
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao processar pagamento."]);
}
