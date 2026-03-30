<?php
    $msg = "";
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nome  = $_POST["nome"];
        $email = $_POST["email"];
        $mat   = $_POST["matricula"];
        $msg   = "";

        echo "nome: " . $nome . " email: " . $email . " matrícula: " . $mat . "<br>";

        // caso o arquivo não existe, criar um e escrever o cabeçalho
        if (!file_exists("alunos.txt")) {
            $arqAluno = fopen("alunos.txt", "w") or die("Erro ao criar arquivo");
            $linha = "nome;email;matricula\n";
            fwrite($arqAluno, $linha);
            fclose($arqAluno);
        }

        // abre o arquivo e grava as informações do aluno
        $arqAluno = fopen("alunos.txt", "a") or die("Erro ao abrir arquivo");
        $linha    = $nome . ";" . $email . ";" . $mat . "\n";
        fwrite($arqAluno, $linha);
        fclose($arqAluno);

        $msg = "Aluno incluído com sucesso!";
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Incluir Aluno</title>
</head>
<body>
    <h1>Incluir Novo Aluno</h1>
    <form action="inclui_aluno.php" method="POST">
        Nome: <input type="text" name="nome"><br><br>
        E‑mail: <input type="text" name="email"><br><br>
        Matrícula: <input type="text" name="matricula"><br><br>
        <input type="submit" value="Incluir Aluno">
    </form>
    <p><?php echo $msg; ?></p>
</body>
</html>