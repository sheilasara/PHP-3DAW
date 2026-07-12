<?php

header('Content-Type: application/json');
session_start();
require_once "conexao.php";

$dados = json_decode(file_get_contents("php://input"), true);

$email = trim($dados['email'] ?? '');
$senha = trim($dados['senha'] ?? '');

if (empty($email) || empty($senha)) {
    echo json_encode(["sucesso" => false, "mensagem" => "Informe email e senha."]);
    exit;
}

$sql = "SELECT id, nome, senha FROM clientes WHERE email = :email";
$stmt = $conexao->prepare($sql);
$stmt->execute([":email" => $email]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cliente && password_verify($senha, $cliente['senha'])) {
    
    $_SESSION['cliente_id'] = $cliente['id'];
    $_SESSION['cliente_nome'] = $cliente['nome'];

    echo json_encode(["sucesso" => true, "mensagem" => "Login realizado com sucesso!", "nome" => $cliente['nome']]);
} else {
    echo json_encode(["sucesso" => false, "mensagem" => "Email ou senha incorretos."]);
}
