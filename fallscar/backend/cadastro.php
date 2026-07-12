<?php

header('Content-Type: application/json');
require_once "conexao.php";

$dados = json_decode(file_get_contents("php://input"), true);

$nome = trim($dados['nome'] ?? '');
$cpf = trim($dados['cpf'] ?? '');
$email = trim($dados['email'] ?? '');
$senha = trim($dados['senha'] ?? '');
$habilitacao = trim($dados['habilitacao'] ?? '');
$endereco = trim($dados['endereco'] ?? '');

if (empty($nome) || empty($cpf) || empty($email) || empty($senha) || empty($habilitacao) || empty($endereco)) {
    echo json_encode(["sucesso" => false, "mensagem" => "Preencha todos os campos."]);
    exit;
}

$sql = "SELECT id FROM clientes WHERE email = :email OR cpf = :cpf";
$stmt = $conexao->prepare($sql);
$stmt->execute([":email" => $email, ":cpf" => $cpf]);

if ($stmt->rowCount() > 0) {
    echo json_encode(["sucesso" => false, "mensagem" => "Ja existe um cadastro com este email ou CPF."]);
    exit;
}

$senhaCriptografada = password_hash($senha, PASSWORD_DEFAULT);

$sql = "INSERT INTO clientes (nome, cpf, email, senha, habilitacao, endereco)
        VALUES (:nome, :cpf, :email, :senha, :habilitacao, :endereco)";
$stmt = $conexao->prepare($sql);
$sucesso = $stmt->execute([
    ":nome" => $nome,
    ":cpf" => $cpf,
    ":email" => $email,
    ":senha" => $senhaCriptografada,
    ":habilitacao" => $habilitacao,
    ":endereco" => $endereco
]);

if ($sucesso) {
    echo json_encode(["sucesso" => true, "mensagem" => "Cadastro realizado com sucesso!"]);
} else {
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao cadastrar. Tente novamente."]);
}
