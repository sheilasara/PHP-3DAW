<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.html');
    exit();
}

$id_pergunta = $_POST['id_pergunta'];
$pergunta = $_POST['pergunta'];
$resposta_correta = $_POST['resposta_correta'];

$arquivo = fopen('perguntas.txt', 'r');
$existe = false;

while (!feof($arquivo)) {
    $linha = fgets($arquivo);
    if (trim($linha) != '') {
        $dados = explode(';', $linha);
        if ($dados[0] == $id_pergunta) {
            $existe = true;
            break;
        }
    }
}
fclose($arquivo);

if ($existe) {
    header('Location: erro.php?mensagem=ID da pergunta já existe!&voltar=criar_pergunta_texto.php');
    exit();
}

$linha_pergunta = $id_pergunta . ";" . $pergunta . ";" . "texto" . ";" . $_SESSION['usuario_id'] . "\n";
$arquivo_perguntas = fopen('perguntas.txt', 'a');
fwrite($arquivo_perguntas, $linha_pergunta);
fclose($arquivo_perguntas);

$linha_resposta = $id_pergunta . ";" . $resposta_correta . "\n";
$arquivo_respostas = fopen('respostas.txt', 'a');
fwrite($arquivo_respostas, $linha_resposta);
fclose($arquivo_respostas);

header('Location: sucesso.php?mensagem=Pergunta de texto criada com sucesso!&link=menu.php');
?>