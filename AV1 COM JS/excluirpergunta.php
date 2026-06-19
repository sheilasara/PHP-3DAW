<!DOCTYPE html>
<html>
<head>
    <title>Excluir Pergunta</title>
    <link rel="stylesheet" href="style.css">

    <script>

        function validarExclusao(){

            let id =
                document.getElementById(
                    "idPergunta"
                ).value;

            if(id === ""){

                alert(
                    "Selecione uma pergunta."
                );

                return false;
            }

            return confirm(
                "Tem certeza que deseja excluir esta pergunta? Esta ação não pode ser desfeita."
            );
        }

    </script>

</head>

<body>

<div class="container">

    <h1>Excluir Pergunta</h1>

    <a href="menu.php">
        ← Voltar ao Menu
    </a>

<?php

if(
    isset($_POST['confirmar']) &&
    isset($_POST['id'])
){

    $idExcluir =
        trim($_POST['id']);

    if(!is_numeric($idExcluir)){

        echo
        "<p class='error'>
            ID inválido.
        </p>";

    } else {

        $todasPerguntas = [];

        $excluida = false;

        $perguntaExcluida = "";

        $arqLeitura =
            fopen(
                "perguntas.txt",
                "r"
            );

        while(!feof($arqLeitura)){

            $linha =
                fgets($arqLeitura);

            if(
                !empty(trim($linha))
            ){

                $dados =
                    explode(
                        ";",
                        trim($linha)
                    );

                if(
                    $dados[0]
                    != $idExcluir
                ){

                    $todasPerguntas[] =
                        trim($linha);

                } else {

                    $excluida = true;

                    $perguntaExcluida =
                        $dados[2];
                }
            }
        }

        fclose($arqLeitura);

        if($excluida){

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
                Pergunta excluída com sucesso!
                <br>
                <strong>Pergunta removida:</strong>
                "
                .
                htmlspecialchars(
                    $perguntaExcluida
                )
                .
            "</p>";

        } else {

            echo
            "<p class='error'>
                Pergunta não encontrada.
            </p>";
        }
    }
}

$perguntas = [];

$arqLista =
    fopen(
        "perguntas.txt",
        "r"
    );

while(!feof($arqLista)){

    $linha =
        fgets($arqLista);

    if(
        !empty(trim($linha))
    ){

        $dados =
            explode(
                ";",
                trim($linha)
            );

        $perguntas[] = [

            "id" =>
                $dados[0],

            "tipo" =>
                $dados[1],

            "pergunta" =>
                $dados[2]
        ];
    }
}

fclose($arqLista);

?>

<?php if(count($perguntas) > 0): ?>

<div class="form-box">

    <h3>
        Selecione a Pergunta para Excluir
    </h3>

    <form
        method="POST"
        onsubmit="return validarExclusao()"
    >

        <label>
            Escolha a pergunta:
        </label>

        <select
            id="idPergunta"
            name="id"
            required
        >

            <option value="">
                Selecione...
            </option>

            <?php foreach($perguntas as $p): ?>

                <option
                    value="<?php echo $p['id']; ?>"
                >

                    ID <?php echo $p['id']; ?>

                    -

                    <?php
                    echo
                    (
                        $p['tipo']
                        == 'multipla'
                    )
                    ?
                    '[Múltipla]'
                    :
                    '[Discursiva]';
                    ?>

                    -

                    <?php

                    if(
                        strlen(
                            $p['pergunta']
                        ) > 100
                    ){

                        echo
                        substr(
                            $p['pergunta'],
                            0,
                            100
                        )
                        . "...";

                    } else {

                        echo
                        $p['pergunta'];
                    }

                    ?>

                </option>

            <?php endforeach; ?>

        </select>

        <br><br>

        <button
            type="submit"
            name="confirmar"
            style="background-color:#dc3545;"
        >
            Excluir Pergunta
        </button>

    </form>

</div>

<h3>
    Todas as Perguntas Cadastradas
</h3>

<table
    border="1"
    width="100%"
>

    <tr>

        <th>ID</th>

        <th>Tipo</th>

        <th>Pergunta</th>

    </tr>

    <?php foreach($perguntas as $p): ?>

        <tr>

            <td>
                <?php echo $p['id']; ?>
            </td>

            <td>

                <?php

                echo
                (
                    $p['tipo']
                    == 'multipla'
                )
                ?
                'Múltipla Escolha'
                :
                'Discursiva';

                ?>

            </td>

            <td>

                <?php

                echo
                htmlspecialchars(
                    $p['pergunta']
                );

                ?>

            </td>

        </tr>

    <?php endforeach; ?>

</table>

<?php else: ?>

<p class="error">

    Nenhuma pergunta cadastrada para excluir.

</p>

<?php endif; ?>

</div>

</body>
</html>
