<?php

session_start();


if (!isset($_SESSION['cliente_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(["sucesso" => false, "mensagem" => "Voce precisa estar logado para continuar."]);
    exit;
}
