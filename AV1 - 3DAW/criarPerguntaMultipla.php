<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.html');
    exit();
}

$arquivo = fopen('perguntas.txt', 'r');
$contador = 1;
while (fgets($arquivo)) {
    $contador++;
}
fclose($arquivo);
$id_pergunta = $contador;

$pergunta = $_POST['pergunta'];
$opcao1 = $_POST['opcao1'];
$opcao2 = $_POST['opcao2'];
$opcao3 = $_POST['opcao3'];
$opcao4 = $_POST['opcao4'];
$correta = $_POST['correta'];

$linha_pergunta = $id_pergunta . ";" . $pergunta . ";" . "mc" . ";" . $_SESSION['usuario_id'] . "\n";
$arquivo_perguntas = fopen('perguntas.txt', 'a');
fwrite($arquivo_perguntas, $linha_pergunta);
fclose($arquivo_perguntas);

$linha_resposta = $id_pergunta . ";" . $opcao1 . ";" . $opcao2 . ";" . $opcao3 . ";" . $opcao4 . ";" . $correta . "\n";
$arquivo_respostas = fopen('respostas.txt', 'a');
fwrite($arquivo_respostas, $linha_resposta);
fclose($arquivo_respostas);

header('Location: sucesso.php?mensagem=Pergunta criada com sucesso!&link=menu.php');
?>