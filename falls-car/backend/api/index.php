<?php
/**
 * Ponto de entrada único da API (front controller), conforme a estrutura
 * sugerida no documento de requisitos (/backend/api).
 *
 * Toda a lógica de roteamento fica em /backend/routes/api.php; este
 * arquivo apenas delega para lá, mantendo a pasta /api como a raiz
 * pública que deve ser apontada no VirtualHost/DocumentRoot do servidor.
 */
require_once __DIR__ . '/../routes/api.php';
