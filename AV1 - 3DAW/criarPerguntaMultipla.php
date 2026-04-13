    //ainda está incompleto



<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pergunta = $_POST["id_pergunta"];
    $pergunta = $_POST["pergunta"];
    $opcao1 = $_POST["opcao1"];
    $opcao2 = $_POST["opcao2"];
    $opcao3 = $_POST["opcao3"];
    $correta = $_POST["correta"];
    
    // Criar arquivo perguntas.txt se não existir
    if (!file_exists("perguntas.txt")) {
        $arqPerguntas = fopen("perguntas.txt", "w") or die("Erro ao criar arquivo");
        $linha = "id_pergunta;pergunta;tipo;criador\n";
        fwrite($arqPerguntas, $linha);
        fclose($arqPerguntas);
    }
    
    fclose($arqPerguntas);
    
    if ($existe) {
        echo "ID da pergunta já existe!<br>";
        echo "<a href='criar_pergunta_mc.html'>Voltar</a>";
        exit();
    }
    
    // salva a pergunta
    $arqPerguntas = fopen("perguntas.txt", "a") or die("Erro ao abrir arquivo");
    $linha = $id_pergunta . ";" . $pergunta . ";" . "mc" . ";" . $_SESSION['usuario_id'] . "\n";
    fwrite($arqPerguntas, $linha);
    fclose($arqPerguntas);
    
    if (!file_exists("respostas.txt")) {
        $arqRespostas = fopen("respostas.txt", "w") or die("Erro ao criar arquivo");
        $linha = "id_pergunta;opcao1;opcao2;opcao3;correta\n";
        fwrite($arqRespostas, $linha);
        fclose($arqRespostas);
    }
    
    // salva as respostas
    $arqRespostas = fopen("respostas.txt", "a") or die("Erro ao abrir arquivo");
    $linha = $id_pergunta . ";" . $opcao1 . ";" . $opcao2 . ";" . $opcao3 . ";" . $opcao4 . ";" . $correta . "\n";
    fwrite($arqRespostas, $linha);
    fclose($arqRespostas);
    
    echo "Pergunta de múltipla escolha criada com sucesso!<br>";
    echo "<a href='menu.php'>Voltar ao menu</a><br>";
    echo "<a href='criar_pergunta_mc.html'>Criar outra pergunta</a>";
}


?>
