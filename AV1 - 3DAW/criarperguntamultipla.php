<!DOCTYPE html>
<html>
<head>
    <title>Criar Pergunta Multipla Escolha</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Criar Pergunta de Multipla Escolha</h1>
        <a href="menu.php">Voltar ao Menu</a>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $pergunta = $_POST['pergunta'];
            $opcao_a = $_POST['opcao_a'];
            $opcao_b = $_POST['opcao_b'];
            $opcao_c = $_POST['opcao_c'];
            $opcao_d = $_POST['opcao_d'];
            $resposta_correta = $_POST['resposta_correta'];
            
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
            
            $linha = $id . ";multipla;" . $pergunta . ";" . $opcao_a . ";" . $opcao_b . ";" . $opcao_c . ";" . $opcao_d . ";" . $resposta_correta . "\n";
            
            $arqQuest = fopen("perguntas.txt", "a") or die("Erro ao abrir arquivo");
            fwrite($arqQuest, $linha);
            fclose($arqQuest);
            
            echo "<p class='success'>Pergunta de multipla escolha criada com sucesso! ID: " . $id . "</p>";
        }
        ?>
        
        <div class="form-box">
            <form method="POST">
                <label>Pergunta:</label>
                <textarea name="pergunta" rows="4" required></textarea><br>
                
                <label>Opcao A:</label>
                <input type="text" name="opcao_a" required><br>
                
                <label>Opcao B:</label>
                <input type="text" name="opcao_b" required><br>
                
                <label>Opcao C:</label>
                <input type="text" name="opcao_c" required><br>
                
                <label>Opcao D:</label>
                <input type="text" name="opcao_d" required><br>
                
                <label>Resposta Correta:</label>
                <select name="resposta_correta" required>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select><br>
                
                <button type="submit">Criar Pergunta</button>
            </form>
        </div>
    </div>
</body>
</html>