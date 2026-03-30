<?php
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome  = $_POST["nome"];
    $email = $_POST["email"];
    $mat   = $_POST["matricula"];

    if (!file_exists("alunos.txt")) {
        $msg = "Arquivo de alunos não existe!";
    } else {
        $linhas = file("alunos.txt");
        $novoConteudo = "";
        $alterado = false;

        foreach ($linhas as $linha) {
            $dados = explode(";", trim($linha));

            // mantém cabeçalho
            if ($dados[0] == "nome") {
                $novoConteudo .= $linha;
                continue;
            }

            // se encontrou a matrícula, altera
            if (isset($dados[2]) && $dados[2] == $mat) {
                $novoConteudo .= $nome . ";" . $email . ";" . $mat . "\n";
                $alterado = true;
            } else {
                $novoConteudo .= $linha;
            }
        }

        if ($alterado) {
            $arqAluno = fopen("alunos.txt", "w") or die("Erro ao abrir arquivo");
            fwrite($arqAluno, $novoConteudo);
            fclose($arqAluno);

            $msg = "Aluno alterado com sucesso!";
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
    <title>Alterar Aluno</title>
</head>
<body>
    <h1>Alterar Aluno</h1>
    <form action="alterar_aluno.php" method="POST">
        Matrícula (para localizar): <input type="text" name="matricula"><br><br>
        Novo Nome: <input type="text" name="nome"><br><br>
        Novo E-mail: <input type="text" name="email"><br><br>
        <input type="submit" value="Alterar Aluno">
    </form>
    <p><?php echo $msg; ?></p>
</body>
</html>