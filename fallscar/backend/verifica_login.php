<?php

header('Content-Type: application/json');
session_start();

if (isset($_SESSION['cliente_id'])) {
    echo json_encode(["logado" => true, "nome" => $_SESSION['cliente_nome']]);
} else {
    echo json_encode(["logado" => false]);
}
