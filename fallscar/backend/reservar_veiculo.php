<?php


header('Content-Type: application/json');
require_once "verifica_sessao.php"; 
require_once "conexao.php";

$dados = json_decode(file_get_contents("php://input"), true);

$clienteId = $_SESSION['cliente_id'];
$veiculoId = intval($dados['veiculo_id'] ?? 0);
$lojaRetiradaId = intval($dados['loja_retirada_id'] ?? 0);
$lojaDevolucaoId = intval($dados['loja_devolucao_id'] ?? 0);
$periodoDias = intval($dados['periodo_dias'] ?? 0);
$dataInicio = trim($dados['data_inicio'] ?? '');
$motorista = $dados['motorista'] ?? null; 


if (!$veiculoId || !$lojaRetiradaId || !$lojaDevolucaoId || !$periodoDias || !$dataInicio) {
    echo json_encode(["sucesso" => false, "mensagem" => "Preencha todos os campos da reserva."]);
    exit;
}


$periodosPermitidos = [7, 15, 30];
if (!in_array($periodoDias, $periodosPermitidos)) {
    echo json_encode(["sucesso" => false, "mensagem" => "Periodo invalido. Escolha 7, 15 ou 30 dias."]);
    exit;
}


$sql = "SELECT id, cidade FROM lojas WHERE id IN (:retirada, :devolucao)";
$stmt = $conexao->prepare($sql);
$stmt->execute([":retirada" => $lojaRetiradaId, ":devolucao" => $lojaDevolucaoId]);
$lojas = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // id => cidade

if (!isset($lojas[$lojaRetiradaId]) || !isset($lojas[$lojaDevolucaoId])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Loja de retirada ou devolucao invalida."]);
    exit;
}


if ($lojas[$lojaRetiradaId] !== $lojas[$lojaDevolucaoId]) {
    echo json_encode(["sucesso" => false, "mensagem" => "A devolucao deve ocorrer na mesma cidade da retirada."]);
    exit;
}


$sql = "SELECT valor_diaria, disponivel FROM veiculos WHERE id = :id";
$stmt = $conexao->prepare($sql);
$stmt->execute([":id" => $veiculoId]);
$veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$veiculo) {
    echo json_encode(["sucesso" => false, "mensagem" => "Veiculo nao encontrado."]);
    exit;
}

if ($veiculo['disponivel'] != 1) {
    echo json_encode(["sucesso" => false, "mensagem" => "Este veiculo nao esta mais disponivel."]);
    exit;
}

$dataFim = date('Y-m-d', strtotime($dataInicio . " + $periodoDias days"));
$valorTotal = $veiculo['valor_diaria'] * $periodoDias;

$conexao->beginTransaction();

try {
    
    $sql = "INSERT INTO reservas
                (cliente_id, veiculo_id, loja_retirada_id, loja_devolucao_id,
                 data_inicio, data_fim, periodo_dias, valor_total, status)
            VALUES
                (:cliente_id, :veiculo_id, :loja_retirada_id, :loja_devolucao_id,
                 :data_inicio, :data_fim, :periodo_dias, :valor_total, 'pendente')";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([
        ":cliente_id" => $clienteId,
        ":veiculo_id" => $veiculoId,
        ":loja_retirada_id" => $lojaRetiradaId,
        ":loja_devolucao_id" => $lojaDevolucaoId,
        ":data_inicio" => $dataInicio,
        ":data_fim" => $dataFim,
        ":periodo_dias" => $periodoDias,
        ":valor_total" => $valorTotal
    ]);

    $reservaId = $conexao->lastInsertId();

    
    if (!empty($motorista) && !empty($motorista['nome'])) {
        $sql = "INSERT INTO motoristas (reserva_id, nome, cpf, habilitacao)
                VALUES (:reserva_id, :nome, :cpf, :habilitacao)";
        $stmt = $conexao->prepare($sql);
        $stmt->execute([
            ":reserva_id" => $reservaId,
            ":nome" => $motorista['nome'],
            ":cpf" => $motorista['cpf'] ?? '',
            ":habilitacao" => $motorista['habilitacao'] ?? ''
        ]);
    }

    
    $sql = "UPDATE veiculos SET disponivel = 0 WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([":id" => $veiculoId]);

    $conexao->commit();

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Reserva criada! Agora finalize o pagamento.",
        "reserva_id" => $reservaId,
        "valor_total" => $valorTotal
    ]);

} catch (Exception $e) {
    
    $conexao->rollBack();
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao criar reserva. Tente novamente."]);
}
