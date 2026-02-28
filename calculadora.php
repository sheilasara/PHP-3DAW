<?php
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $numero1 = $_POST["valorA"];
        $numero2 = $_POST["valorB"];
        $operacao = $_POST["operacao"];
        $resultado = null;

        switch ($operacao) {
            case 'soma':
                $resultado = $numero1 + $numero2;
                break;
            case 'subtrair':
                $resultado = $numero1 - $numero2;
                break;
            case 'multiplicar':
                $resultado = $numero1 * $numero2;
                break;
            case 'divisao':
                if ($numero2 != 0) {
                    $resultado = $numero1 / $numero2;
                } else {
                    $resultado = 'Erro: divisão por zero.';
                }
                break;
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Calculadora</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            text-align: center;
            margin-top: 50px;
        }

        .container {
            background-color: white;
            padding: 20px;
            width: 300px;
            margin: auto;
            border: 1px solid #ccc;
            color: #000080;
        }

        input {
            width: 90%;
            padding: 5px;
            margin: 5px 0;
            border-radius: 5px;
        }

        button {
            padding: 5px 10px;
            margin: 5px;
            border-radius: 5px;
            background-color: #ADD8E6;
        }

        h1 {
            font-size: 22px;
            color: #6699CC;
        }

        .resultado {
            margin-top: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Calculadora</h1>

    <form method="POST" action="">
        Insira o primeiro valor:<br>
        <input type="number" step="any" name="valorA" required><br>

        Insira o segundo valor:<br>
        <input type="number" step="any" name="valorB" required><br><br>

        <button type="submit" name="operacao" value="somar">Somar</button>
        <button type="submit" name="operacao" value="subtrair">Subtrair</button>
        <button type="submit" name="operacao" value="multiplicar">Multiplicar</button>
        <button type="submit" name="operacao" value="dividir">Dividir</button>
    </form>

    <?php if ($resultado !== null): ?>
        <div class="resultado">
            Resultado: <?php echo $resultado; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>