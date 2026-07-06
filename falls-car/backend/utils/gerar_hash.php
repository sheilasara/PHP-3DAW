<?php
if ($argc < 2) {
    echo "Uso: php gerar_hash.php <senha>\n";
    exit(1);
}

echo password_hash($argv[1], PASSWORD_BCRYPT) . PHP_EOL;
