<!DOCTYPE html>
<html>
<head>
    <title>Alterar Pergunta</title>
    <link rel="stylesheet" href="style.css">

    <script>

        function contemPontoVirgula(texto){
            return texto.includes(";");
        }

        function validarFormulario(){

            let pergunta =
                document.querySelector(
                    "[name='pergunta']"
                ).value.trim();

            if(pergunta.length < 10){
                alert(
                    "A pergunta deve possuir pelo menos 10 caracteres."
                );
                return false;
            }

            if(pergunta.length > 500){
                alert(
                    "A pergunta deve possuir no máximo 500 caracteres."
                );
                return false;
            }

            if(contemPontoVirgula(pergunta)){
                alert(
                    "O caractere ';' não é permitido."
                );
                return false;
            }

            let tipo =
                document.querySelector(
                    "[name='tipo']"
                ).value;

            if(tipo == "multipla"){

                let a =
                    document.querySelector(
                        "[name='opcao_a']"
                    ).value.trim();

                let b =
                    document.querySelector(
                        "[name='opcao_b']"
                    ).value.trim();

                let c =
                    document.querySelector(
                        "[name='opcao_c']"
                    ).value.trim();

                let d =
                    document.querySelector(
                        "[name='opcao_d']"
                    ).value.trim();

                if(
                    a == "" ||
                    b == "" ||
                    c == "" ||
                    d == ""
                ){
                    alert(
                        "Todas as alternativas devem ser preenchidas."
                    );
                    return false;
                }

                if(
                    contemPontoVirgula(a) ||
                    contemPontoVirgula(b) ||
                    contemPontoVirgula(c) ||
                    contemPontoVirgula(d)
                ){
                    alert(
                        "O caractere ';' não é permitido."
                    );
                    return false;
                }

                let opcoes = [
                    a.toLowerCase(),
                    b.toLowerCase(),
                    c.toLowerCase(),
                    d.toLowerCase()
                ];

                if(
                    new Set(opcoes).size != 4
                ){
                    alert(
                        "As alternativas não podem ser iguais."
                    );
                    return false;
                }

            }else{

                let resposta =
                    document.querySelector(
                        "[name='resposta_esperada']"
                    ).value.trim();

                if(resposta.length < 3){
                    alert(
                        "A resposta esperada deve possuir pelo menos 3 caracteres."
                    );
                    return false;
                }

                if(resposta.length > 300){
                    alert(
                        "A resposta esperada deve possuir no máximo 300 caracteres."
                    );
                    return false;
                }

                if(
                    contemPontoVirgula(resposta)
                ){
                    alert(
                        "O caractere ';' não é permitido."
                    );
                    return false;
                }
            }

            return confirm(
                "Deseja realmente salvar as alterações?"
            );
        }

    </script>

</head>

<body>

<div class="container">

<h1>Alterar Pergunta</h1>

<a href="menu.php">
    ← Voltar ao Menu
</a>

<?php

$perguntas = [];

$arqLeitura =
    fopen("perguntas.txt", "r")
    or die("Erro ao abrir arquivo");

while(!feof($arqLeitura)){

    $linha = fgets($arqLeitura);

    if(!empty(trim($linha))){

        $dados =
            explode(
                ";",
                trim($linha)
            );

        $perguntas[] = $dados;
    }
}

fclose($arqLeitura);
?>
<?php

if (
    $_SERVER['REQUEST_METHOD'] == 'POST'
    && isset($_POST['alterar'])
) {

    $id =
        trim($_POST['id']);

    $tipo =
        trim($_POST['tipo']);

    $erro = "";

    if($tipo == "multipla"){

        $pergunta =
            trim($_POST['pergunta']);

        $opcao_a =
            trim($_POST['opcao_a']);

        $opcao_b =
            trim($_POST['opcao_b']);

        $opcao_c =
            trim($_POST['opcao_c']);

        $opcao_d =
            trim($_POST['opcao_d']);

        $resposta_correta =
            $_POST['resposta_correta'];

        if(strlen($pergunta) < 10){

            $erro =
                "A pergunta deve possuir pelo menos 10 caracteres.";

        } elseif(strlen($pergunta) > 500){

            $erro =
                "A pergunta deve possuir no máximo 500 caracteres.";

        } elseif(strpos($pergunta, ";") !== false){

            $erro =
                "O caractere ';' não é permitido.";

        } elseif(
            strpos($opcao_a, ";") !== false ||
            strpos($opcao_b, ";") !== false ||
            strpos($opcao_c, ";") !== false ||
            strpos($opcao_d, ";") !== false
        ){

            $erro =
                "O caractere ';' não é permitido nas alternativas.";

        } else {

            $opcoes = [
                strtolower($opcao_a),
                strtolower($opcao_b),
                strtolower($opcao_c),
                strtolower($opcao_d)
            ];

            if(
                count(array_unique($opcoes))
                != 4
            ){
                $erro =
                    "As alternativas não podem ser iguais.";
            }
        }

        $novaLinha =
            $id .
            ";multipla;" .
            $pergunta .
            ";" .
            $opcao_a .
            ";" .
            $opcao_b .
            ";" .
            $opcao_c .
            ";" .
            $opcao_d .
            ";" .
            $resposta_correta;

    } else {

        $pergunta =
            trim($_POST['pergunta']);

        $resposta_esperada =
            trim($_POST['resposta_esperada']);

        if(strlen($pergunta) < 10){

            $erro =
                "A pergunta deve possuir pelo menos 10 caracteres.";

        } elseif(strlen($pergunta) > 500){

            $erro =
                "A pergunta deve possuir no máximo 500 caracteres.";

        } elseif(strpos($pergunta, ";") !== false){

            $erro =
                "O caractere ';' não é permitido.";

        } elseif(
            strlen($resposta_esperada) < 3
        ){

            $erro =
                "A resposta esperada deve possuir pelo menos 3 caracteres.";

        } elseif(
            strlen($resposta_esperada) > 300
        ){

            $erro =
                "A resposta esperada deve possuir no máximo 300 caracteres.";

        } elseif(
            strpos(
                $resposta_esperada,
                ";"
            ) !== false
        ){

            $erro =
                "O caractere ';' não é permitido.";

        }

        $novaLinha =
            $id .
            ";texto;" .
            $pergunta .
            ";" .
            $resposta_esperada;
    }

    if(empty($erro)){

        $todasPerguntas = [];

        $arqLeitura2 =
            fopen(
                "perguntas.txt",
                "r"
            );

        while(!feof($arqLeitura2)){

            $linha =
                fgets($arqLeitura2);

            if(
                !empty(trim($linha))
            ){
                $todasPerguntas[] =
                    trim($linha);
            }
        }

        fclose($arqLeitura2);

        foreach(
            $todasPerguntas
            as $key => $linhaExistente
        ){

            $dadosExistente =
                explode(
                    ";",
                    $linhaExistente
                );

            if(
                $dadosExistente[0]
                == $id
            ){

                $todasPerguntas[$key] =
                    $novaLinha;

                break;
            }
        }

        $arqGravacao =
            fopen(
                "perguntas.txt",
                "w"
            );

        foreach(
            $todasPerguntas
            as $linha
        ){

            fwrite(
                $arqGravacao,
                $linha . "\n"
            );
        }

        fclose($arqGravacao);

        echo
        "<p class='success'>
            Pergunta alterada com sucesso!
        </p>";

        header(
            "refresh:2;url=alterarpergunta.php"
        );

    } else {

        echo
        "<p class='error'>
            $erro
        </p>";
    }
}
?>
<?php

$perguntaEdit = null;

if(isset($_GET['id'])){

    $idBusca =
        trim($_GET['id']);

    foreach($perguntas as $p){

        if($p[0] == $idBusca){

            $perguntaEdit = $p;

            break;
        }
    }
}

?>

<!-- LISTA DE PERGUNTAS -->

<h2>Perguntas Cadastradas</h2>

<table border="1" width="100%">

    <tr>
        <th>ID</th>
        <th>Tipo</th>
        <th>Pergunta</th>
        <th>Ação</th>
    </tr>

    <?php foreach($perguntas as $p): ?>

        <tr>

            <td>
                <?php echo $p[0]; ?>
            </td>

            <td>

                <?php

                echo (
                    $p[1] == "multipla"
                )
                ?
                "Múltipla Escolha"
                :
                "Discursiva";

                ?>

            </td>

            <td>

                <?php

                if(
                    strlen($p[2]) > 80
                ){

                    echo
                    substr(
                        $p[2],
                        0,
                        80
                    ) . "...";

                } else {

                    echo $p[2];
                }

                ?>

            </td>

            <td>

                <a href="?id=<?php echo $p[0]; ?>">
                    Editar
                </a>

            </td>

        </tr>

    <?php endforeach; ?>

</table>

<br>
<?php if($perguntaEdit): ?>

<div class="form-box">

    <h3>
        Editando Pergunta ID:
        <?php echo $perguntaEdit[0]; ?>
    </h3>

    <form
        method="POST"
        onsubmit="return validarFormulario()"
    >

        <input
            type="hidden"
            name="id"
            value="<?php echo $perguntaEdit[0]; ?>"
        >

        <input
            type="hidden"
            name="tipo"
            value="<?php echo $perguntaEdit[1]; ?>"
        >

        <label>Pergunta:</label>

        <textarea
            name="pergunta"
            rows="4"
            maxlength="500"
            required
        ><?php echo htmlspecialchars($perguntaEdit[2]); ?></textarea>

        <br>

        <?php if($perguntaEdit[1] == "multipla"): ?>

            <label>Opção A:</label>

            <input
                type="text"
                name="opcao_a"
                maxlength="150"
                value="<?php echo htmlspecialchars($perguntaEdit[3]); ?>"
                required
            >

            <br>

            <label>Opção B:</label>

            <input
                type="text"
                name="opcao_b"
                maxlength="150"
                value="<?php echo htmlspecialchars($perguntaEdit[4]); ?>"
                required
            >

            <br>

            <label>Opção C:</label>

            <input
                type="text"
                name="opcao_c"
                maxlength="150"
                value="<?php echo htmlspecialchars($perguntaEdit[5]); ?>"
                required
            >

            <br>

            <label>Opção D:</label>

            <input
                type="text"
                name="opcao_d"
                maxlength="150"
                value="<?php echo htmlspecialchars($perguntaEdit[6]); ?>"
                required
            >

            <br>

            <label>Resposta Correta:</label>

            <select
                name="resposta_correta"
                required
            >

                <option
                    value="A"
                    <?php echo ($perguntaEdit[7] == "A") ? "selected" : ""; ?>
                >
                    A
                </option>

                <option
                    value="B"
                    <?php echo ($perguntaEdit[7] == "B") ? "selected" : ""; ?>
                >
                    B
                </option>

                <option
                    value="C"
                    <?php echo ($perguntaEdit[7] == "C") ? "selected" : ""; ?>
                >
                    C
                </option>

                <option
                    value="D"
                    <?php echo ($perguntaEdit[7] == "D") ? "selected" : ""; ?>
                >
                    D
                </option>

            </select>

            <br>

        <?php else: ?>

            <label>
                Resposta Esperada:
            </label>

            <textarea
                name="resposta_esperada"
                rows="3"
                maxlength="300"
                required
            ><?php echo htmlspecialchars($perguntaEdit[3]); ?></textarea>

            <br>

        <?php endif; ?>

        <button
            type="submit"
            name="alterar"
        >
            Salvar Alterações
        </button>

    </form>

</div>

<?php endif; ?>

</div>

</body>
</html>
