<?php
session_start();

$id = $_POST['id'];
$email = $_POST['email'];

// checa se o usuário existe
$arquivo = fopen('usuarios.txt', 'r');
$existe = false;

while (!feof($arquivo)) {
    $linha = fgets($arquivo);
    if (trim($linha) != '') {
        $dados = explode(';', $linha);
        if ($dados[0] == $id) {
            $existe = true;
            break;
        }
    }
}
fclose($arquivo);

if ($existe) {
    echo "Esse ID já existe";
    echo "<a href='index.html'>Voltar</a>";
} else {
    $linha = $id . ";" . $email . ";" . $senha . "\n";
    $arquivo = fopen('usuarios.txt', 'a');
    fwrite($arquivo, $linha);
    fclose($arquivo);
    
    echo "Cadastrado!";
    echo "<a href='index.html'>Fazer login</a>";
}
?>