<?php
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mat = $_POST["matricula"];

    if (!file_exists("alunos.txt")) {
        $msg = "Arquivo de alunos não existe!";
    } else {
        $linhas = file("alunos.txt");
        $novoConteudo = "";
        $excluido = false;

        foreach ($linhas as $linha) {
            $dados = explode(";", trim($linha));

            // mantém o cabeçalho
            if ($dados[0] == "nome") {
                $novoConteudo .= $linha;
                continue;
            }

            // se esse não for o aluno/matrícula a ser excluído, mantenha
            if (isset($dados[2]) && $dados[2] != $mat) {
                $novoConteudo .= $linha;
            } else {
                $excluido = true;
            }
        }

        if ($excluido) {
            $arqAluno = fopen("alunos.txt", "w") or die("Erro ao abrir arquivo");
            fwrite($arqAluno, $novoConteudo);
            fclose($arqAluno);

            $msg = "Aluno excluído com sucesso!";
        } else {
            $msg = "Matrícula não encontrada!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Excluir Aluno</title>
</head>
<body>
    <h1>Excluir Aluno</h1>
    <form action="excluir_aluno.php" method="POST">
        Matrícula: <input type="text" name="matricula"><br><br>
        <input type="submit" value="Excluir Aluno">
    </form>
    <p><?php echo $msg; ?></p>
</body>
</html>