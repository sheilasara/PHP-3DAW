<?php
/**
 * Cabeçalhos de CORS para permitir que o frontend (servido separadamente,
 * ex: em outra porta/servidor) consuma a API.
 *
 * Em produção, substitua '*' pelo domínio real do frontend.
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Requisições "preflight" do navegador não precisam de processamento.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
