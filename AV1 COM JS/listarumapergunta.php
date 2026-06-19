<!DOCTYPE html>
<html>
<head>
    <title>Listar uma Pergunta</title>
    <link rel="stylesheet" href="style.css">

    <script>

        function validarBusca(){

            let id =
                document.getElementById("idBusca").value;

            if(id == ""){

                alert("Informe um ID.");

                return false;
            }

            if(id <= 0){

                alert(
                    "O ID deve ser maior que zero."
                );

                return false;
            }

            return true;
        }

    </script>

</head>

<body>

<div class="container">

    <h1>Buscar Pergunta Específica</h1>

    <a href="menu.php">
        ← Voltar ao Menu
    </a>

    <div class="form-box">

        <form
            method="GET"
            onsubmit="return validarBusca()"
        >

            <label>
                ID da Pergunta:
            </label>

            <input
                type="number"
                id="idBusca"
                name="id"
                min="1"
                required
            >

            <button type="submit">
                Buscar
            </button>

        </form>

    </div>

<?php

if(
    isset($_GET['id']) &&
    !empty($_GET['id'])
){

    $idBusca =
        trim($_GET['id']);

    if(!is_numeric($idBusca)){

        echo
        "<p class='error'>
            ID inválido.
        </p>";

    } else {

        $encontrada = false;

        $arqQuest =
            fopen(
                "perguntas.txt",
                "r"
            )
            or die(
                "Erro ao abrir arquivo"
            );

        while(!feof($arqQuest)){

            $linha =
                fgets($arqQuest);

            if(
                !empty(trim($linha))
            ){

                $colunaDados =
                    explode(
                        ";",
                        trim($linha)
                    );

                if(
                    $colunaDados[0]
                    == $idBusca
                ){

                    $encontrada = true;

                    ?>

                    <div class="pergunta-detalhe">

                        <h2>
                            Detalhes da Pergunta
                        </h2>

                        <p>
                            <strong>ID:</strong>
                            <?php echo $colunaDados[0]; ?>
                        </p>

                        <p>
                            <strong>Tipo:</strong>

                            <?php

                            echo
                            (
                                $colunaDados[1]
                                == "multipla"
                            )
                            ?
                            "Múltipla Escolha"
                            :
                            "Discursiva";

                            ?>

                        </p>

                        <p>
                            <strong>Pergunta:</strong>
                        </p>

                        <div class="pergunta-texto">

                            <?php

                            echo nl2br(
                                htmlspecialchars(
                                    $colunaDados[2]
                                )
                            );

                            ?>

                        </div>

                        <?php if($colunaDados[1] == "multipla"): ?>

                            <p>
                                <strong>
                                    Opções:
                                </strong>
                            </p>

                            <ul class="opcoes">

                                <li>
                                    <strong>A)</strong>
                                    <?php echo htmlspecialchars($colunaDados[3]); ?>
                                </li>

                                <li>
                                    <strong>B)</strong>
                                    <?php echo htmlspecialchars($colunaDados[4]); ?>
                                </li>

                                <li>
                                    <strong>C)</strong>
                                    <?php echo htmlspecialchars($colunaDados[5]); ?>
                                </li>

                                <li>
                                    <strong>D)</strong>
                                    <?php echo htmlspecialchars($colunaDados[6]); ?>
                                </li>

                            </ul>

                            <p>

                                <strong>
                                    Resposta Correta:
                                </strong>

                                <?php echo htmlspecialchars($colunaDados[7]); ?>

                            </p>

                        <?php else: ?>

                            <p>

                                <strong>
                                    Resposta Esperada:
                                </strong>

                            </p>

                            <div class="resposta-esperada">

                                <?php

                                echo nl2br(
                                    htmlspecialchars(
                                        $colunaDados[3]
                                    )
                                );

                                ?>

                            </div>

                        <?php endif; ?>

                    </div>

                    <?php

                    break;
                }
            }
        }

        fclose($arqQuest);

        if(!$encontrada){

            echo
            "<p class='error'>
                Pergunta não encontrada.
            </p>";
        }
    }
}

?>

</div>

</body>
</html>
