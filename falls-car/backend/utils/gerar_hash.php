<?php
/**
 * Utilitário de linha de comando para gerar um hash BCRYPT de senha,
 * usado para popular o seed.sql com um hash válido.
 *
 * Uso: php gerar_hash.php minhasenha123
 */
if ($argc < 2) {
    echo "Uso: php gerar_hash.php <senha>\n";
    exit(1);
}

echo password_hash($argv[1], PASSWORD_BCRYPT) . PHP_EOL;
