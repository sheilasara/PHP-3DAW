<!DOCTYPE html>
<html>
<head>
    <title>Criar Pergunta Discursiva</title>
    <link rel="stylesheet" href="style.css">

    <script>

        function contemPontoVirgula(texto){
            return texto.includes(";");
        }

        function validarFormulario(){

            let pergunta =
                document.getElementById("pergunta").value.trim();

            let resposta =
                document.getElementById("resposta").value.trim();

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
                    "O caractere ';' não é permitido na pergunta."
                );
                return false;
            }

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

            if(contemPontoVirgula(resposta)){
                alert(
                    "O caractere ';' não é permitido na resposta esperada."
                );
                return false;
            }

            return true;
        }

    </script>

</head>

<body>

    <div class="container">

        <h1>Criar Pergunta Discursiva (Texto)</h1>

        <a href="menu.php">Voltar ao Menu</a>

        <?php

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $pergunta = trim($_POST['pergunta']);
            $resposta_esperada =
                trim($_POST['resposta_esperada']);

            $erro = "";

            if(strlen($pergunta) < 10){

                $erro =
                    "A pergunta deve possuir pelo menos 10 caracteres.";

            } elseif(strlen($pergunta) > 500){

                $erro =
                    "A pergunta deve possuir no máximo 500 caracteres.";

            } elseif(strpos($pergunta, ";") !== false){

                $erro =
                    "O caractere ';' não é permitido na pergunta.";

            } elseif(strlen($resposta_esperada) < 3){

                $erro =
                    "A resposta esperada deve possuir pelo menos 3 caracteres.";

            } elseif(strlen($resposta_esperada) > 300){

                $erro =
                    "A resposta esperada deve possuir no máximo 300 caracteres.";

            } elseif(strpos($resposta_esperada, ";") !== false){

                $erro =
                    "O caractere ';' não é permitido na resposta esperada.";

            }

            if(empty($erro)){

                $id = 1;

                if(file_exists("perguntas.txt")){

                    $arqLeitura =
                        fopen("perguntas.txt", "r");

                    while(!feof($arqLeitura)){

                        $linha = fgets($arqLeitura);

                        if(!empty(trim($linha))){
                            $id++;
                        }
                    }

                    fclose($arqLeitura);
                }

                $linha =
                    $id .
                    ";texto;" .
                    $pergunta .
                    ";" .
                    $resposta_esperada .
                    "\n";

                $arqQuest =
                    fopen("perguntas.txt", "a")
                    or die("Erro ao abrir arquivo");

                fwrite($arqQuest, $linha);

                fclose($arqQuest);

                echo
                    "<p class='success'>
                        Pergunta discursiva criada com sucesso!
                        ID: $id
                    </p>";

            } else {

                echo
                    "<p class='error'>
                        $erro
                    </p>";
            }
        }

        ?>

        <div class="form-box">

            <form
                method="POST"
                onsubmit="return validarFormulario()"
            >

                <label>Pergunta:</label>

                <textarea
                    id="pergunta"
                    name="pergunta"
                    rows="4"
                    required
                    maxlength="500"
                ></textarea>

                <br>

                <label>Resposta Esperada:</label>

                <textarea
                    id="resposta"
                    name="resposta_esperada"
                    rows="3"
                    required
                    maxlength="300"
                ></textarea>

                <br>

                <button type="submit">
                    Criar Pergunta
                </button>

            </form>

        </div>

    </div>

</body>
</html>
```
