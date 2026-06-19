<!DOCTYPE html>
<html>
<head>
    <title>Listar Todas as Perguntas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Listar Todas as Perguntas</h1>
        <a href="menu.php">← Voltar ao Menu</a>
        
        <?php
        $arqQuest = fopen("perguntas.txt", "r") or die("Erro ao abrir arquivo");
        $contador = 1;
        ?>
        
        <table border="1" width="100%">
            <tr>
                <th>#</th>
                <th>ID</th>
                <th>Tipo</th>
                <th>Pergunta</th>
                <th>Respostas</th>
            </tr>
            <?php
            while(!feof($arqQuest)) {
                $linha = fgets($arqQuest);
                if(!empty(trim($linha))) {
                    $colunaDados = explode(";", trim($linha));
                    
                    echo "<tr>";
                    echo "<td>" . $contador . "</td>";
                    echo "<td>" . $colunaDados[0] . "</td>";
                    echo "<td>" . ($colunaDados[1] == "multipla" ? "Múltipla Escolha" : "Discursiva") . "</td>";
                    echo "<td>" . $colunaDados[2] . "</td>";
                    
                    echo "<td>";
                    if($colunaDados[1] == "multipla") {
                        echo "<strong>Opções:</strong><br>";
                        echo "A) " . $colunaDados[3] . "<br>";
                        echo "B) " . $colunaDados[4] . "<br>";
                        echo "C) " . $colunaDados[5] . "<br>";
                        echo "D) " . $colunaDados[6] . "<br>";
                        echo "<strong> Resposta Correta: " . $colunaDados[7] . "</strong>";
                    } else {
                        echo "<strong> Resposta Esperada:</strong><br>";
                        echo $colunaDados[3];
                    }
                    echo "</td>";
                    
                    echo "</tr>";
                    $contador++;
                }
            }
            
            fclose($arqQuest);
            
            if($contador == 1) {
                echo "<tr><td colspan='5' style='text-align: center;'>Nenhuma pergunta cadastrada ainda.</td></tr>";
            }
            ?>
        </table>
        <br>
        <p>Total de perguntas: <?php echo $contador - 1; ?></p>
    </div>
</body>
</html>
