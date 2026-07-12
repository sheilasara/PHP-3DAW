<?php

$host = "localhost";
$nomeBanco = "locadora_fallscar";
$usuario = "root";
$senha = ""; 

try {
    $conexao = new PDO("mysql:host=$host;dbname=$nomeBanco;charset=utf8", $usuario, $senha);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $erro) {
  
    header('Content-Type: application/json');
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao conectar no banco: " . $erro->getMessage()]);
    exit;
}
