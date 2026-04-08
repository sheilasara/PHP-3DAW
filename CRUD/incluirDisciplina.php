<?php
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome  = $_POST["nome"];
    $sigla = $_POST["sigla"];
    $carga = $_POST["carga"];

    if (empty($nome) || empty($sigla) || empty($carga)) {
        $msg = "Erro: campos em branco.";
    } else {
        if (!file_exists("disciplinas.txt")) {
            $arqDisc = fopen("disciplinas.txt", "w") or die("Erro ao criar arquivo");
            $linha = "nome;sigla;carga\n";
            fwrite($arqDisc, $linha);
            fclose($arqDisc);
        }

        $arqDisc = fopen("disciplinas.txt", "a") or die("Erro ao abrir arquivo");
        $linha = $nome . ";" . $sigla . ";" . $carga . "\n";
        fwrite($arqDisc, $linha);
        fclose($arqDisc);

        $msg = "A disciplina foi cadastrada!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
<h1>Criar Nova Disciplina</h1>

<form action="IncluirDisciplina.php" method="POST">
    Nome: <input type="text" name="nome">
    <br><br>
    Sigla: <input type="text" name="sigla">
    <br><br>
    Carga Horaria: <input type="text" name="carga">
    <br><br>
    <input type="submit" value="Criar Nova Disciplina">
</form>

<p><?php echo $msg ?></p>

</body>
</html>
