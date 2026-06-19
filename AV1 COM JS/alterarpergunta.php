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
```
