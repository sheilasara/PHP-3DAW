<?php

header('Content-Type: application/json');
require_once "conexao.php";


$sql = "SELECT v.id, v.modelo, v.marca, v.categoria, v.ano, v.cor, v.valor_diaria,
               v.imagem, v.loja_id, l.nome AS loja_nome, l.cidade AS loja_cidade
        FROM veiculos v
        INNER JOIN lojas l ON v.loja_id = l.id
        WHERE v.disponivel = 1
        ORDER BY v.categoria, v.modelo";
$stmt = $conexao->query($sql);
$veiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);


$sqlLojas = "SELECT id, nome, cidade FROM lojas ORDER BY cidade, nome";
$stmtLojas = $conexao->query($sqlLojas);
$lojas = $stmtLojas->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "sucesso" => true,
    "veiculos" => $veiculos,
    "lojas" => $lojas
]);
