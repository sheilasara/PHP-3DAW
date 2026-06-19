<!DOCTYPE html>
<html>
<head>
    <title>Criar Pergunta Multipla Escolha</title>
    <link rel="stylesheet" href="style.css">

    <script>

        function contemPontoVirgula(texto){
            return texto.includes(";");
        }

        function validarFormulario(){

            let pergunta =
                document.getElementById("pergunta").value.trim();

            let a =
                document.getElementById("opcaoA").value.trim();

            let b =
                document.getElementById("opcaoB").value.trim();

            let c =
                document.getElementById("opcaoC").value.trim();

            let d =
                document.getElementById("opcaoD").value.trim();

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

            if(
                a.length == 0 ||
                b.length == 0 ||
                c.length == 0 ||
                d.length == 0
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
                    "O caractere ';' não é permitido nas alternativas."
                );
                return false;
            }

            let opcoes = [
                a.toLowerCase(),
                b.toLowerCase(),
                c.toLowerCase(),
                d.toLowerCase()
            ];

            let unicas = new Set(opcoes);

            if(unicas.size != 4){
                alert(
                    "As alternativas não podem ser iguais."
                );
                return false;
            }

            return true;
        }

    </script>

</head>

<body>

    <div class="container">

        <h1>Criar Pergunta de Multipla Escolha</h1>

        <a href="menu.php">Voltar ao Menu</a>

        <?php

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

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

            $erro = "";

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

                if(count(array_unique($opcoes)) != 4){

                    $erro =
                        "As alternativas não podem ser iguais.";
                }
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
                    $resposta_correta .
                    "\n";

                $arqQuest =
                    fopen("perguntas.txt", "a")
                    or die("Erro ao abrir arquivo");

                fwrite($arqQuest, $linha);

                fclose($arqQuest);

                echo
                    "<p class='success'>
                        Pergunta de múltipla escolha criada com sucesso!
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

                <label>Opção A:</label>

                <input
                    type="text"
                    id="opcaoA"
                    name="opcao_a"
                    maxlength="150"
                    required
                >

                <br>

                <label>Opção B:</label>

                <input
                    type="text"
                    id="opcaoB"
                    name="opcao_b"
                    maxlength="150"
                    required
                >

                <br>

                <label>Opção C:</label>

                <input
                    type="text"
                    id="opcaoC"
                    name="opcao_c"
                    maxlength="150"
                    required
                >

                <br>

                <label>Opção D:</label>

                <input
                    type="text"
                    id="opcaoD"
                    name="opcao_d"
                    maxlength="150"
                    required
                >

                <br>

                <label>Resposta Correta:</label>

                <select
                    name="resposta_correta"
                    required
                >
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>

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
