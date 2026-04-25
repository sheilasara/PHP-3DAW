<!DOCTYPE html>
<html>
<head>
    <title>Criar Pergunta Discursiva</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Criar Pergunta Discursiva (Texto)</h1>
        <a href="menu.php">Voltar ao Menu</a>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $pergunta = $_POST['pergunta'];
            $resposta_esperada = $_POST['resposta_esperada'];
            
            // Gerar ID automatico
            $id = 1;
            if(file_exists("perguntas.txt")) {
                $arqLeitura = fopen("perguntas.txt", "r");
                while(!feof($arqLeitura)) {
                    $linha = fgets($arqLeitura);
                    if(!empty(trim($linha))) {
                        $id++;
                    }
                }
                fclose($arqLeitura);
            }
            
            $linha = $id . ";texto;" . $pergunta . ";" . $resposta_esperada . "\n";
            
            $arqQuest = fopen("perguntas.txt", "a") or die("Erro ao abrir arquivo");
            fwrite($arqQuest, $linha);
            fclose($arqQuest);
            
            echo "<p class='success'>Pergunta discursiva criada com sucesso! ID: " . $id . "</p>";
        }
        ?>
        
        <div class="form-box">
            <form method="POST">
                <label>Pergunta:</label>
                <textarea name="pergunta" rows="4" required></textarea><br>
                
                <label>Resposta Esperada:</label>
                <textarea name="resposta_esperada" rows="3" required></textarea><br>
                
                <button type="submit">Criar Pergunta</button>
            </form>
        </div>
    </div>
</body>
</html>
